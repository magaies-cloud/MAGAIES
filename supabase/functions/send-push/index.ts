// Supabase Edge Function: send-push
// Called by Postgres triggers (via pg_net) when rows are inserted into
// notifications / chat_messages / call_invites. Sends a data-only, high-priority
// FCM message to every registered device of the recipient so it arrives even
// when the app is closed.
import { createClient } from "npm:@supabase/supabase-js@2";

const WEBHOOK_SECRET = Deno.env.get("PUSH_WEBHOOK_SECRET") ?? "";
const SA = JSON.parse(Deno.env.get("FIREBASE_SERVICE_ACCOUNT") ?? "{}");

const supabase = createClient(
  Deno.env.get("SUPABASE_URL")!,
  Deno.env.get("SUPABASE_SERVICE_ROLE_KEY")!,
);

// ---------- Google OAuth (service account JWT, RS256 via WebCrypto) ----------
let cachedToken: { value: string; exp: number } | null = null;

function b64url(input: ArrayBuffer | string): string {
  const bytes = typeof input === "string" ? new TextEncoder().encode(input) : new Uint8Array(input);
  let bin = "";
  for (const b of bytes) bin += String.fromCharCode(b);
  return btoa(bin).replace(/\+/g, "-").replace(/\//g, "_").replace(/=+$/, "");
}

async function getAccessToken(): Promise<string> {
  const now = Math.floor(Date.now() / 1000);
  if (cachedToken && cachedToken.exp - 60 > now) return cachedToken.value;

  const header = b64url(JSON.stringify({ alg: "RS256", typ: "JWT" }));
  const claim = b64url(JSON.stringify({
    iss: SA.client_email,
    scope: "https://www.googleapis.com/auth/firebase.messaging",
    aud: "https://oauth2.googleapis.com/token",
    iat: now,
    exp: now + 3600,
  }));
  const pem = (SA.private_key as string)
    .replace(/-----BEGIN PRIVATE KEY-----|-----END PRIVATE KEY-----|\n/g, "");
  const der = Uint8Array.from(atob(pem), (c) => c.charCodeAt(0));
  const key = await crypto.subtle.importKey(
    "pkcs8", der, { name: "RSASSA-PKCS1-v1_5", hash: "SHA-256" }, false, ["sign"],
  );
  const sig = await crypto.subtle.sign("RSASSA-PKCS1-v1_5", key, new TextEncoder().encode(`${header}.${claim}`));
  const jwt = `${header}.${claim}.${b64url(sig)}`;

  const res = await fetch("https://oauth2.googleapis.com/token", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: `grant_type=urn:ietf:params:oauth:grant-type:jwt-bearer&assertion=${jwt}`,
  });
  const json = await res.json();
  if (!json.access_token) throw new Error(`OAuth failed: ${JSON.stringify(json)}`);
  cachedToken = { value: json.access_token, exp: now + (json.expires_in ?? 3600) };
  return json.access_token;
}

// ---------- Helpers ----------
async function displayName(userId: string): Promise<string> {
  const { data: store } = await supabase.from("stores").select("name, name_ar").eq("owner_id", userId).limit(1).maybeSingle();
  if (store?.name_ar || store?.name) return store.name_ar || store.name;
  const { data: user } = await supabase.from("users").select("name, name_ar").eq("id", userId).maybeSingle();
  return user?.name_ar || user?.name || "مستخدم";
}

interface DeviceTarget {
  token: string;
  deviceType: string;
}

async function tokensFor(userId: string): Promise<DeviceTarget[]> {
  const map = new Map<string, string>();
  const { data } = await supabase
    .from("user_device_tokens")
    .select("fcm_token, device_type")
    .eq("user_id", userId);
  data?.forEach((r) => {
    if (r.fcm_token) {
      map.set(r.fcm_token, String(r.device_type ?? "android").toLowerCase());
    }
  });
  const { data: u } = await supabase.from("users").select("fcm_token").eq("id", userId).maybeSingle();
  if (u?.fcm_token && !map.has(u.fcm_token)) {
    map.set(u.fcm_token, "android");
  }
  return Array.from(map.entries()).map(([token, deviceType]) => ({ token, deviceType }));
}

async function sendToUser(userId: string, data: Record<string, string>, ttlSeconds = 86400) {
  const targets = await tokensFor(userId);
  if (targets.length === 0) return { sent: 0, reason: "no_tokens" };
  const accessToken = await getAccessToken();
  let sent = 0;
  const isSilentControl = data.type === "call" || data.type === "call_cancel";

  for (const { token, deviceType } of targets) {
    const isIos = deviceType === "ios";
    const messagePayload: Record<string, unknown> = {
      token,
      data,
      android: { priority: "HIGH", ttl: `${ttlSeconds}s` },
    };

    // On iOS, APNs requires an explicit alert + sound payload so notifications
    // appear in the notification curtain even when the app is completely closed.
    if (isIos && !isSilentControl) {
      const alertTitle = data.title || "مقاييس";
      const alertBody = data.body || "";
      messagePayload.notification = {
        title: alertTitle,
        body: alertBody,
      };
      messagePayload.apns = {
        headers: {
          "apns-priority": "10",
          "apns-push-type": "alert",
        },
        payload: {
          aps: {
            alert: {
              title: alertTitle,
              body: alertBody,
            },
            sound: "magaiees_notify.wav",
            badge: 1,
            "mutable-content": 1,
          },
        },
      };
    } else if (isIos && isSilentControl) {
      messagePayload.apns = {
        headers: {
          "apns-priority": "10",
          "apns-push-type": "background",
        },
        payload: {
          aps: {
            "content-available": 1,
          },
        },
      };
    }

    const res = await fetch(`https://fcm.googleapis.com/v1/projects/${SA.project_id}/messages:send`, {
      method: "POST",
      headers: { Authorization: `Bearer ${accessToken}`, "Content-Type": "application/json" },
      body: JSON.stringify({ message: messagePayload }),
    });
    if (res.ok) {
      sent++;
    } else {
      const err = await res.text();
      console.error("FCM error", res.status, err);
      if (err.includes("UNREGISTERED") || err.includes("INVALID_ARGUMENT")) {
        await supabase.from("user_device_tokens").delete().eq("fcm_token", token);
      }
    }
  }
  return { sent, total: targets.length };
}

// ---------- Handlers ----------
async function handleNotification(r: Record<string, any>) {
  if (!r.user_id) return { skipped: "no user" };
  return sendToUser(r.user_id, {
    type: String(r.type ?? "general"),
    title: String(r.title_ar ?? r.title ?? "مقاييس"),
    body: String(r.body_ar ?? r.body ?? ""),
    related_id: String(r.related_id ?? ""),
  });
}

async function handleChat(r: Record<string, any>) {
  const { data: room } = await supabase.from("chat_rooms")
    .select("buyer_id, merchant_id").eq("id", r.room_id).maybeSingle();
  if (!room) return { skipped: "no room" };
  const recipient = r.sender_id === room.buyer_id ? room.merchant_id : room.buyer_id;
  if (!recipient || recipient === r.sender_id) return { skipped: "no recipient" };
  const sender = await displayName(r.sender_id);
  const body = r.type === "image" ? "📷 صورة" : String(r.content ?? "").slice(0, 200);
  return sendToUser(recipient, {
    type: "chat",
    title: `💬 ${sender}`,
    body,
    room_id: String(r.room_id),
    sender_id: String(r.sender_id),
  });
}

async function handleCall(r: Record<string, any>) {
  if (!r.callee_id || r.status !== "ringing") return { skipped: "not ringing" };
  return sendToUser(r.callee_id, {
    type: "call",
    invite_id: String(r.id),
    room_id: String(r.room_id),
    caller_id: String(r.caller_id),
    caller_name: String(r.caller_name ?? "مستخدم"),
  }, 45); // a stale call ring is useless; expire quickly
}

async function handleCallCancel(r: Record<string, any>) {
  if (!r.callee_id) return { skipped: "no callee" };
  return sendToUser(r.callee_id, {
    type: "call_cancel",
    invite_id: String(r.id),
    room_id: String(r.room_id),
    caller_name: String(r.caller_name ?? "مستخدم"),
    status: String(r.status ?? "ended"),
  }, 120);
}

Deno.serve(async (req) => {
  if (req.headers.get("x-webhook-secret") !== WEBHOOK_SECRET || !WEBHOOK_SECRET) {
    return new Response("unauthorized", { status: 401 });
  }
  try {
    const { kind, record } = await req.json();
    let result;
    if (kind === "notification") result = await handleNotification(record);
    else if (kind === "chat") result = await handleChat(record);
    else if (kind === "call") result = await handleCall(record);
    else if (kind === "call_cancel") result = await handleCallCancel(record);
    else result = { skipped: "unknown kind" };
    return Response.json(result);
  } catch (e) {
    console.error(e);
    return Response.json({ error: String(e) }, { status: 500 });
  }
});

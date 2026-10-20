# Magaiees ERP (ELSAJANA2) — Core Speed & Stability Rules

## 1. Fast Execution & Instant Deployment Rules (قواعد السرعة والتنفيذ المباشر)
- **Active Repository Path**: Always work directly inside `C:\xampp\htdocs\ELSAJANA2\magaies`.
- **Use `run_command` Directly**: Shell execution via `run_command` works natively in <1 second. **NEVER** create temporary PHP scripts (`_sync_deploy_tmp*.php`, `_diag_tmp.php`) or use `read_url_content` over `localhost` to execute Git or terminal commands.
- **Local-Only Changes by Default (`التعديل في المحل فقط — ممنوع الرفع التلقائي`)**:
  - **CRITICAL**: All code edits, fixes, and new features MUST be applied **locally (`في المحل فقط`)** for instant speed.
  - **NEVER** push to GitHub (`git push`) or deploy to the live server (`trigger_webhook.php` / `c.elsajana.com`) automatically after editing files.
  - Do NOT touch GitHub or the live server unless the user **explicitly instructs** you to push/deploy.
- **Instant 1-Step Deploy — ONLY When Explicitly Requested ("نرفع من المحل" / "ارفع للموقع الحي والجيت هاب")**:
  **ONLY** when explicitly asked by the user to push/deploy local changes to GitHub (`origin/main`) and the live server (`https://c.elsajana.com`), run a **single command** in `C:\xampp\htdocs\ELSAJANA2\magaies`:
  ```powershell
  git add -u; if ((git status --porcelain)) { git commit -m "Sync updates from local" }; git push origin main; C:\xampp\php\php.exe trigger_webhook.php
  ```
  Do NOT spend steps researching deployment mechanisms or spawning subagents for a push request.
- **Fast Targeted Search**:
  - **NEVER** run `Get-ChildItem -Recurse` or scan `uploads/`, `baileys_auth_*`, `node_modules/`, `pwa_dist/`, or `scratch/`.
  - Use `git grep -n "<pattern>"` inside `C:\xampp\htdocs\ELSAJANA2\magaies` or read the known file directly with `view_file`.
  - Avoid spawning subagents (`invoke_subagent`) for straightforward edits, bug fixes, or deployments.

## 2. Project Rules
- If you ever temporarily disable a feature or functionality (for example, for local testing), you MUST automatically re-enable it before finishing the task. Do not leave features disabled unless explicitly requested by the user.

## 3. WhatsApp & OTP Stability Rules
- **CRITICAL**: Do NOT change the Baileys `browser` config. It MUST remain `Browsers.macOS('Desktop')` to prevent WhatsApp from blocking the server and causing infinite `'INITIALIZING'` loops.
- **CRITICAL**: Do NOT use the native `qrcode.toDataURL()` package for QR codes on the server, as it causes a Segmentation Fault and crashes the Node.js process. Always use the external API fallback (`https://api.qrserver.com`) and send the image URL to the frontend.

#!/bin/bash
# ╔══════════════════════════════════════════════════════════════╗
# ║  WhatsApp Service Permanent Lock & Protection Script         ║
# ║  Prevents any Git Pull or Upload from overwriting the file   ║
# ╚══════════════════════════════════════════════════════════════╝

# 1. Get the directory of the script
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"
FILE="$DIR/whatsapp-service.mjs"

if [ ! -f "$FILE" ]; then
    echo "❌ Error: whatsapp-service.mjs not found at $FILE"
    exit 1
fi

echo "🔒 Protecting whatsapp-service.mjs..."

# 2. Tell Git to ignore upstream changes to this file on the server (skip-worktree)
if git rev-parse --is-inside-work-tree >/dev/null 2>&1; then
    git update-index --skip-worktree "$FILE"
    echo "✅ Git skip-worktree enabled (Git pulls will ignore changes to this file)."
else
    echo "⚠️ Warning: Not inside a Git repository. Skipping Git lock."
fi

# 3. Make the file read-only on the file system
chmod 444 "$FILE"
echo "✅ File permissions set to Read-Only (Prevents accidental overwrites via FTP/Uploads)."

# 4. Create a backup just in case
cp "$FILE" "$FILE.bak"
echo "✅ Created local backup: whatsapp-service.mjs.bak"

echo "🎯 WhatsApp Service is now SOLID and locked against overwrites!"
echo "💡 To unlock in the future to make changes, run: chmod 644 whatsapp-service.mjs && git update-index --no-skip-worktree whatsapp-service.mjs"

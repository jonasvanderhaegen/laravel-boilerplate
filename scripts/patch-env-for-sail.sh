#!/bin/sh
set -e

echo "⚙️  Non-destructive patching of .env for Laravel Sail..."
touch .env

# Each line: KEY|OLD|NEW
PATCH_DATA='
APP_URL||http://localhost
DB_CONNECTION|sqlite|pgsql
DB_HOST|127.0.0.1|pgsql
DB_PORT|3306|5432
DB_DATABASE||laravel
DB_USERNAME|root|sail
DB_PASSWORD||password
SESSION_DRIVER|database|redis
QUEUE_CONNECTION|database|redis
CACHE_STORE|database|redis
REDIS_HOST|127.0.0.1|valkey
MAIL_MAILER|log|smtp
MAIL_HOST|127.0.0.1|mailpit
MAIL_PORT|2525|1025
'

echo "$PATCH_DATA" | while IFS='|' read -r KEY OLD NEW; do
  # strip leading/trailing whitespace from KEY
  KEY=$(printf "%s" "$KEY" | awk '{$1=$1};1')

  if grep -q "^[[:space:]]*${KEY}=" .env; then
    CURRENT=$(grep "^[[:space:]]*${KEY}=" .env | head -n1 | cut -d '=' -f2-)
    if [ -z "$OLD" ] || [ "$CURRENT" = "$OLD" ]; then
      echo "🔁 Replacing $KEY ($CURRENT → $NEW)"
      sed -i.bak "s|^[[:space:]]*${KEY}=.*|${KEY}=${NEW}|" .env
    else
      echo "⏭  Skipping $KEY (custom value: $CURRENT)"
    fi
  else
    echo "➕ Adding $KEY=${NEW}"
    printf '%s=%s\n' "$KEY" "$NEW" >> .env
  fi
done

rm -f .env.bak
echo "✅ .env patched non-destructively."

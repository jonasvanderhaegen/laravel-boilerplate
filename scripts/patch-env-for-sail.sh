#!/usr/bin/env bash
set -e

echo "Non-destructive patching of .env for Laravel Sail..."

# Define patch rules: key | old_value | new_value
PATCHES=(
  "APP_URL||http://localhost"
  "DB_CONNECTION|sqlite|pgsql"
  "DB_HOST|127.0.0.1|pgsql"
  "DB_PORT|3306|5432"
  "DB_DATABASE||laravel"
  "DB_USERNAME|root|sail"
  "DB_PASSWORD||password"
  "SESSION_DRIVER|database|redis"
  "QUEUE_CONNECTION|database|redis"
  "CACHE_STORE|database|redis"
  "REDIS_HOST|127.0.0.1|valkey"
  "MAIL_MAILER|log|smtp"
  "MAIL_HOST|127.0.0.1|mailpit"
  "MAIL_PORT|2525|1025"
)

# ensure .env exists
[ -f .env ] || cp .env.example .env

for entry in "${PATCHES[@]}"; do
  # split into exactly three parts on '|'
  IFS='|' read -r KEY OLD NEW <<< "$entry"

  # skip any completely empty or malformed entry
  if [[ -z "$KEY" ]]; then
    echo "Skipping empty patch entry"
    continue
  fi

  if grep -qE "^\s*${KEY}=" .env; then
    CURRENT=$(grep -E "^\s*${KEY}=" .env | cut -d '=' -f2-)

    # only replace if it matches the OLD value (or if OLD is empty)
    if [[ -z "$OLD" || "$CURRENT" == "$OLD" ]]; then
      echo "Replacing $KEY ($CURRENT → $NEW)"
      sed -i.bak "s|^${KEY}=.*|${KEY}=${NEW}|" .env
    else
      echo "⏭  Skipping $KEY (custom value: $CURRENT)"
    fi
  else
    echo "➕ Adding $KEY=${NEW}"
    echo "${KEY}=${NEW}" >> .env
  fi
done

rm -f .env.bak
echo ".env patched non-destructively."

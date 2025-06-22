#!/bin/bash

set -e

echo "🛠️  Initializing Laravel Sail environment..."

# 1. Ensure .env exists and patch for Sail
if [ ! -f ".env" ]; then
  if [ -f ".env.example" ]; then
    echo "📄 Copying .env from .env.example..."
    cp .env.example .env
    ./scripts/patch-env-for-sail.sh
  else
    echo "❌ .env.example not found. Aborting."
    exit 1
  fi
fi

# 2. Install composer deps using Docker if Sail doesn't exist yet
if [ ! -f "vendor/bin/sail" ]; then
  echo "📦 Installing PHP dependencies via Docker..."
  docker run --rm -u "$(id -u):$(id -g)" \
    -v "$(pwd)":/var/www/html \
    -w /var/www/html \
    laravelsail/php84-composer:latest \
    composer install
else
  echo "✅ vendor/ already present."
fi

echo
read -r -p "⚙️  Would you like to create a 'sail' alias in your shell config? [Y/n]: " add_alias </dev/tty
if [[ "$add_alias" =~ ^[Yy]?$ ]]; then
  SHELL_NAME=$(basename "$SHELL")

  case "$SHELL_NAME" in
    bash)
      TARGET_FILE="$HOME/.bashrc"
      ;;
    zsh)
      TARGET_FILE="$HOME/.zshrc"
      ;;
    fish)
      TARGET_FILE="$HOME/.config/fish/config.fish"
      ;;
    sh|ash)
    TARGET_FILE="$HOME/.profile"
      ;;
    *)
      echo "❗ Unsupported shell ($SHELL_NAME). Please add manually:"
      echo 'sail() { [ -f ./vendor/bin/sail ] && bash ./vendor/bin/sail "$@" || echo "Sail not available"; }'
      exit 0
      ;;
  esac

  if grep -q "sail()" "$TARGET_FILE"; then
    echo "✅ 'sail' function already exists in $TARGET_FILE"
  else
    echo "🔧 Adding 'sail' function and aliases to $TARGET_FILE..."
    if [[ "$SHELL_NAME" == "fish" ]]; then
      cat <<'EOF' >> "$TARGET_FILE"

function sail
  if test -f ./vendor/bin/sail
    bash ./vendor/bin/sail $argv
  else
    echo "❌ vendor/bin/sail not found"
  end
end

alias s 'sail '
alias sa 'sail artisan '
alias sc 'sail composer '
alias sm 'sail artisan migrate:fresh --seed'
EOF
    else
      cat <<'EOF' >> "$TARGET_FILE"

# Laravel Sail function and shortcuts
sail() {
  if [ -f ./vendor/bin/sail ]; then
    bash ./vendor/bin/sail "$@"
  else
    echo "❌ vendor/bin/sail not found"
  fi
}

alias s='sail '
alias sa='sail artisan '
alias sc='sail composer '
alias sm='sa migrate'
alias smf='sa migrate:fresh'
alias smfs='sa migrate:fresh --seed'
alias sus='s up -d'
alias sus='s stop'

EOF
    fi
    echo "✅ Aliases added. Run 'source $TARGET_FILE' or restart your terminal to activate them."
  fi
else
  echo "ℹ️  Skipped alias setup. You can still use './vendor/bin/sail'"
fi

# 3. Start containers
echo "🐳 Starting Sail containers..."
./vendor/bin/sail up -d

# 4. Laravel application setup
echo "🔑 Generating app key..."
./vendor/bin/sail artisan key:generate

echo "🧪 Running migrations..."
./vendor/bin/sail artisan migrate

# Optional seeding
read -p "🌱 Run database seeders? [y/N]: " seed_confirm
if [[ "$seed_confirm" =~ ^[Yy]$ ]]; then
  ./vendor/bin/sail artisan db:seed
fi

# 5. Frontend setup
if [ -f "package.json" ]; then
  echo "📦 Installing frontend dependencies..."
  ./vendor/bin/sail npm install
else
  echo "📦 No package.json found. Skipping frontend setup."
fi

echo "✅ Laravel Sail is ready"

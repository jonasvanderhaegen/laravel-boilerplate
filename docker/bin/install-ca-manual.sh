#!/usr/bin/env bash
# Manual CA installation script

echo "🔐 Installing Certificate Authority on your system..."
echo "This will make your browser trust the SSL certificates."
echo ""

# Detect docker-compose command
if command -v docker-compose &> /dev/null; then
    DOCKER_COMPOSE="docker-compose"
elif docker compose version &> /dev/null 2>&1; then
    DOCKER_COMPOSE="docker compose"
else
    echo "Error: Neither 'docker-compose' nor 'docker compose' command found."
    exit 1
fi

# Check if nginx container is running
if ! $DOCKER_COMPOSE ps nginx 2>/dev/null | grep -q "Up\|running"; then
    echo "❌ Nginx container is not running. Please start it first with:"
    echo "   docker compose up -d"
    exit 1
fi

# Install the CA
echo "Running CA installation..."
./docker/bin/setup-ssl-ca

echo ""
echo "✅ CA installation complete!"
echo ""
echo "⚠️  IMPORTANT: You need to restart your browser for the changes to take effect!"
echo ""
echo "After restarting your browser, visit https://laravel.test"
echo "You should see a green padlock indicating the certificate is trusted."

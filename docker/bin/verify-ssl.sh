#!/usr/bin/env bash
# Verify SSL setup

echo "🔍 Verifying SSL Setup..."
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

echo "1. Checking if containers are running..."
$DOCKER_COMPOSE ps

echo ""
echo "2. Checking if mkcert is installed in nginx container..."
$DOCKER_COMPOSE exec nginx which mkcert

echo ""
echo "3. Checking if CA exists in nginx container..."
$DOCKER_COMPOSE exec nginx ls -la /root/.local/share/mkcert/

echo ""
echo "4. Checking if certificates exist..."
$DOCKER_COMPOSE exec nginx ls -la /etc/nginx/certs/

echo ""
echo "5. Testing nginx configuration..."
$DOCKER_COMPOSE exec nginx nginx -t

echo ""
echo "6. Checking certificate details..."
$DOCKER_COMPOSE exec nginx openssl x509 -in /etc/nginx/certs/cert.crt -text -noout | grep -E "Subject:|Issuer:|Not After"

echo ""
echo "7. Checking if CA is trusted on host (macOS)..."
if [ "$(uname)" == "Darwin" ]; then
    security find-certificate -c "mkcert" /Library/Keychains/System.keychain 2>/dev/null && echo "✅ CA found in system keychain" || echo "❌ CA not found in system keychain"
fi

#!/usr/bin/env bash
# Clean up and start fresh

echo "🧹 Cleaning up Docker environment..."

# Stop and remove containers
docker compose down -v 2>/dev/null || docker-compose down -v 2>/dev/null || true

# Remove any dangling images
docker image prune -f

# Ensure environment variables are set
if [ -f .env ]; then
    # Ensure WWWUSER and WWWGROUP are set
    grep -q "^WWWUSER=" .env || echo "WWWUSER=1000" >> .env
    grep -q "^WWWGROUP=" .env || echo "WWWGROUP=1000" >> .env
else
    echo "⚠️  No .env file found. Please create one from .env.example"
fi

echo "✅ Clean up complete!"
echo ""
echo "Now run: ./setup-https.sh"

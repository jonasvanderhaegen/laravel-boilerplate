#!/usr/bin/env bash
# Quick rebuild script for nginx

echo "🔧 Rebuilding nginx container..."

# Stop nginx container
docker compose stop nginx

# Remove the old container
docker compose rm -f nginx

# Rebuild nginx image
docker compose build --no-cache nginx

# Start all containers
docker compose up -d

echo "✅ Nginx container rebuilt successfully!"
echo ""
echo "Now run: ./docker/bin/setup-domain laravel.test"

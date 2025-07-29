#!/usr/bin/env bash
# Fix node modules issues

echo "🔧 Fixing node modules..."
echo ""
echo "This script ensures node_modules are properly installed in the container."
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

# Check if there are host node_modules
if [ -d "node_modules" ]; then
    echo "Found node_modules on host. These will be ignored by the container."
    echo "The container uses its own isolated node_modules."
fi

echo ""
echo "Installing/updating dependencies in the container..."
$DOCKER_COMPOSE exec laravel.test npm ci || $DOCKER_COMPOSE exec laravel.test npm install

echo ""
echo "✅ Container node_modules are ready!"
echo ""
echo "Remember to always run npm commands inside the container:"
echo "  - make dev"
echo "  - ./sail npm run dev"
echo "  - make shell (then npm run dev)"
echo ""
echo "See docker/NODE_MODULES.md for more information."

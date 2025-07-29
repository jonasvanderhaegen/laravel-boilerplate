#!/usr/bin/env bash
# Sail-like wrapper for running commands in the Laravel container

# Detect docker-compose command
if command -v docker-compose &> /dev/null; then
    DOCKER_COMPOSE="docker-compose"
elif docker compose version &> /dev/null 2>&1; then
    DOCKER_COMPOSE="docker compose"
else
    echo "Error: Neither 'docker-compose' nor 'docker compose' command found."
    exit 1
fi

# If no arguments, show help
if [ $# -eq 0 ]; then
    echo "Usage: ./sail [command] [arguments]"
    echo ""
    echo "Examples:"
    echo "  ./sail artisan migrate"
    echo "  ./sail composer install"
    echo "  ./sail npm install"
    echo "  ./sail npm run dev"
    echo "  ./sail php -v"
    echo "  ./sail bash"
    exit 0
fi

# Run the command in the Laravel container
$DOCKER_COMPOSE exec laravel.test "$@"

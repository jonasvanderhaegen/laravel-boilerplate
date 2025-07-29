#!/bin/bash
set -e

# Default domain from environment or use laravel.test
DOMAIN="${APP_DOMAIN:-laravel.test}"

echo "Starting nginx with domain: $DOMAIN"

# Check if certificates exist, if not generate them
if [ ! -f /etc/nginx/certs/cert.crt ] || [ ! -f /etc/nginx/certs/cert.key ]; then
    echo "SSL certificates not found. Generating certificates for $DOMAIN..."
    /usr/local/bin/generate-cert "$DOMAIN"
fi

# Check if server_name.conf exists, if not use default
if [ -f /var/www/html/docker/nginx/conf/server_name.conf ]; then
    echo "Copying custom domain configuration"
    cp /var/www/html/docker/nginx/conf/server_name.conf /etc/nginx/conf.d/server_name.conf
    # Remove default.conf if custom config exists
    rm -f /etc/nginx/conf.d/default.conf
else
    echo "Using default nginx configuration"
fi

# Execute the CMD
exec "$@"

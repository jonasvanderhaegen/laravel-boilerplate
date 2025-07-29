#!/bin/bash
set -e

DOMAIN=${1:-laravel.test}

echo "Generating certificate for domain: $DOMAIN"

# Initialize mkcert if not already initialized
if [ ! -f /root/.local/share/mkcert/rootCA.pem ]; then
    echo "Initializing mkcert certificate authority..."
    export CAROOT=/root/.local/share/mkcert
    mkcert -install
fi

# Ensure certificate directory exists
mkdir -p /etc/nginx/certs

# Generate certificate
cd /etc/nginx/certs
mkcert -key-file cert.key -cert-file cert.crt "$DOMAIN" "*.$DOMAIN" localhost 127.0.0.1 ::1

# Set proper permissions
chmod 644 cert.crt
chmod 600 cert.key

echo "Certificate generated successfully for $DOMAIN"
echo "  - Certificate: /etc/nginx/certs/cert.crt"
echo "  - Private Key: /etc/nginx/certs/cert.key"

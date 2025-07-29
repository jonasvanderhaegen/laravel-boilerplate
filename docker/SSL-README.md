# Laravel Boilerplate SSL Setup

This enhanced Docker setup provides HTTPS support with trusted SSL certificates for local development.

## Features

- ✅ Automatic HTTPS with trusted certificates
- ✅ Custom domain support
- ✅ Automatic hosts file management
- ✅ Browser-trusted certificates (Chrome, Firefox, Safari)
- ✅ Based on Laravel Sail with nginx proxy
- ✅ Preserves all Laravel Sail functionality

## Quick Start

### 1. Using Default Domain (laravel.test)

```bash
# Build and start containers
docker compose up -d  # or docker-compose up -d

# Setup SSL (first time only)
./docker/bin/setup-ssl-ca  # Install certificate authority
./docker/bin/setup-ssl     # Generate SSL certificate

# Access your app at:
# https://laravel.test
```

**Note:** The scripts automatically detect whether to use `docker compose` or `docker-compose`.

### 2. Using Custom Domain

```bash
# Setup everything with one command
make setup-domain DOMAIN=myapp.test

# Or manually:
./docker/bin/setup-domain myapp.test

# Access your app at:
# https://myapp.test
```

## Available Commands

### Using Make (Recommended)

```bash
make help              # Show all available commands
make build            # Build containers
make up               # Start containers
make down             # Stop containers
make setup-domain     # Setup custom domain (DOMAIN=myapp.test)
make shell            # Access Laravel container
make logs             # View all logs
make fresh-start      # Clean install with domain setup
```

### Using Scripts Directly

```bash
# Setup certificate authority (one time only)
./docker/bin/setup-ssl-ca

# Generate SSL certificate
./docker/bin/setup-ssl [domain]

# Setup complete domain (hosts + SSL)
./docker/bin/setup-domain [domain]
```

## How It Works

1. **Certificate Authority**: Uses `mkcert` to create a local CA that's trusted by your system
2. **SSL Certificates**: Generates valid certificates for your domain
3. **Nginx Proxy**: Routes HTTPS traffic to your Laravel application
4. **Hosts File**: Automatically adds domain entries to `/etc/hosts`

## Environment Variables

Add these to your `.env` file:

```env
APP_URL=https://myapp.test
APP_DOMAIN=myapp.test
APP_PORT=80
APP_HTTPS_PORT=443
```

## Troubleshooting

### Certificate Not Trusted

1. Run `./docker/bin/setup-ssl-ca` to install the CA
2. Restart your browser
3. For Firefox on macOS, restart Firefox after CA installation

### Domain Not Resolving

Check `/etc/hosts` file contains:
```
127.0.0.1 yourdomain.test
::1 yourdomain.test
```

### Port Conflicts

Change ports in `.env`:
```env
APP_PORT=8080
APP_HTTPS_PORT=8443
```

## Browser Support

- ✅ Chrome/Chromium
- ✅ Safari
- ✅ Firefox (may require browser restart)
- ✅ Edge

## Requirements

- Docker & Docker Compose (supports both `docker compose` and `docker-compose`)
- macOS or Linux
- sudo access (for hosts file and CA installation)

## Security Note

The generated certificates are for local development only. Never use these certificates in production.

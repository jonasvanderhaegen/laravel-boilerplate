# Laravel Boilerplate HTTPS Setup

This Docker setup provides HTTPS support for your Laravel application using trusted local certificates.

## Features

- ✅ HTTPS with trusted certificates using mkcert
- ✅ Automatic domain setup in hosts file
- ✅ nginx reverse proxy for SSL termination
- ✅ WebSocket support for Hot Module Replacement (HMR)
- ✅ Automatic certificate trust on macOS and Linux
- ✅ Support for custom domains

## Quick Start

1. **Default domain (laravel.test)**:
   ```bash
   ./docker/bin/setup-ssl
   ```

2. **Custom domain**:
   ```bash
   ./docker/bin/setup-ssl myapp.local
   ```

## What it does

1. **Adds domain to hosts file** - Maps your domain to 127.0.0.1
2. **Updates .env file** - Sets APP_URL to use HTTPS
3. **Builds containers** - Creates nginx container with SSL support
4. **Generates certificates** - Creates trusted SSL certificates using mkcert
5. **Installs CA certificate** - Adds the certificate authority to your system trust store
6. **Configures browsers** - Sets up Firefox and Chrome to trust the certificates

## Manual Setup

If you prefer to set things up manually:

1. **Build and start containers**:
   ```bash
   docker compose build
   docker compose up -d
   ```

2. **Generate certificate**:
   ```bash
   docker compose exec nginx generate-cert yourdomain.test
   ```

3. **Add domain to hosts**:
   ```bash
   echo "127.0.0.1 yourdomain.test" | sudo tee -a /etc/hosts
   ```

4. **Update .env**:
   ```
   APP_URL=https://yourdomain.test
   APP_DOMAIN=yourdomain.test
   ```

## Accessing Your Application

After setup, you can access your application at:
- HTTPS: `https://laravel.test` (or your custom domain)
- HTTP will automatically redirect to HTTPS

## Ports

- HTTP: 80 (redirects to HTTPS)
- HTTPS: 443
- Vite HMR: 5173

## Troubleshooting

1. **Certificate not trusted**: Restart your browser after setup
2. **Domain not resolving**: Check /etc/hosts file
3. **Port conflicts**: Change ports in .env file (APP_PORT, APP_HTTPS_PORT)

## Environment Variables

Add these to your .env file if needed:

```env
APP_DOMAIN=laravel.test
APP_PORT=80
APP_HTTPS_PORT=443
VITE_PORT=5173
```

## Architecture

```
┌─────────────┐     ┌─────────────┐     ┌─────────────┐
│   Browser   │────▶│    nginx    │────▶│   Laravel   │
│   (HTTPS)   │     │   (SSL)     │     │    (HTTP)   │
└─────────────┘     └─────────────┘     └─────────────┘
```

The nginx container handles SSL termination and proxies requests to the Laravel container.

## Notes

- The Laravel container still runs on HTTP internally
- nginx handles all SSL/TLS operations
- Certificates are stored in Docker volumes for persistence
- The setup supports wildcard certificates (*.yourdomain.test)

# SSL Troubleshooting Guide

## Common Issues and Solutions

### 0. "docker-compose: command not found"

**Problem:** Newer Docker versions use `docker compose` instead of `docker-compose`.

**Solution:** Our scripts automatically detect the correct command, but if you're running commands manually:
```bash
# Instead of:
docker-compose up -d

# Use:
docker compose up -d

# Or use our wrapper:
./docker/bin/dc up -d
```

### 1. Certificate Shows as Invalid/Untrusted

**Symptoms:**
- Browser shows "Not Secure" or certificate warning
- Certificate appears self-signed

**Solutions:**
```bash
# Install the certificate authority
./docker/bin/setup-ssl-ca

# Regenerate certificates
docker-compose exec nginx rm -rf /etc/nginx/certs/*
./docker/bin/setup-ssl yourdomain.test

# Restart containers
docker-compose restart
```

### 2. "Permission Denied" Errors

**When running setup scripts:**
```bash
chmod +x ./docker/bin/setup-*
chmod +x ./setup-https.sh
```

**When updating hosts file:**
- Scripts require sudo access
- Run with: `sudo ./docker/bin/setup-domain yourdomain.test`

### 3. Domain Not Resolving

**Check hosts file:**
```bash
cat /etc/hosts | grep yourdomain.test
```

**Manually add if missing:**
```bash
echo "127.0.0.1 yourdomain.test" | sudo tee -a /etc/hosts
echo "::1 yourdomain.test" | sudo tee -a /etc/hosts
```

### 4. Port Already in Use

**Error:** "bind: address already in use"

**Solution 1 - Change ports in .env:**
```env
APP_PORT=8080
APP_HTTPS_PORT=8443
```

**Solution 2 - Find and stop conflicting service:**
```bash
# Find what's using port 443
sudo lsof -i :443

# Stop local nginx/apache if running
sudo nginx -s stop
sudo apachectl stop
```

### 5. Firefox Not Trusting Certificate

**macOS specific:**
1. Quit Firefox completely
2. Run: `./docker/bin/setup-ssl-ca`
3. Start Firefox
4. Navigate to `about:config`
5. Search for `security.enterprise_roots.enabled`
6. Set to `true`

### 6. Certificate for Wrong Domain

**If certificate was generated for wrong domain:**
```bash
# Remove old certificates
docker-compose exec nginx rm -rf /etc/nginx/certs/*

# Generate new ones
./docker/bin/setup-ssl correct-domain.test

# Update .env
# APP_DOMAIN=correct-domain.test

# Restart
docker-compose restart
```

### 7. Mixed Content Warnings

**Ensure APP_URL uses https in .env:**
```env
APP_URL=https://yourdomain.test
```

**Clear Laravel cache:**
```bash
docker-compose exec laravel.test php artisan config:clear
docker-compose exec laravel.test php artisan cache:clear
```

### 8. WebSocket Connection Failed

**For Laravel Echo/Pusher over HTTPS:**
```javascript
// In your JavaScript
window.Echo = new Echo({
    broadcaster: 'pusher',
    key: process.env.MIX_PUSHER_APP_KEY,
    cluster: process.env.MIX_PUSHER_APP_CLUSTER,
    forceTLS: true,
    wsHost: window.location.hostname,
    wsPort: 443,
    wssPort: 443,
    disableStats: true,
    enabledTransports: ['ws', 'wss']
});
```

### 9. Container Can't Find mkcert

**Rebuild the nginx container:**
```bash
docker-compose build --no-cache nginx
docker-compose up -d
```

### 10. SSL Certificate Expired

**Regenerate certificates:**
```bash
docker-compose exec nginx sh -c "cd /etc/nginx/certs && mkcert -key-file cert.key -cert-file cert.crt yourdomain.test *.yourdomain.test localhost 127.0.0.1 ::1"
docker-compose restart nginx
```

## Debug Commands

**Check nginx logs:**
```bash
docker compose logs -f nginx  # or docker-compose logs -f nginx
```

**Check certificate details:**
```bash
docker compose exec nginx openssl x509 -in /etc/nginx/certs/cert.crt -text -noout
```

**Test nginx configuration:**
```bash
docker compose exec nginx nginx -t
```

**Check if mkcert is installed:**
```bash
docker compose exec nginx which mkcert
```

## Need More Help?

1. Check nginx error logs: `docker-compose logs nginx`
2. Verify all containers are running: `docker-compose ps`
3. Ensure you're using the latest version of Docker
4. Try a complete fresh start: `make clean && make fresh-start`

## Node Modules Issues

### 11. "Cannot find module" Errors

**Problem:** Getting errors like `Cannot find module @rollup/rollup-linux-arm64-gnu`

**Solution:** You're likely running npm commands on your host instead of in the container.
```bash
# Always use:
make dev
./sail npm run dev

# Never use:
npm run dev  # This runs on host and causes issues
```

### 12. Node modules out of sync

**Solution:** The container maintains its own node_modules:
```bash
make fix-node-modules
# or
./sail npm ci
```

See [NODE_MODULES.md](NODE_MODULES.md) for detailed information about the isolation strategy.

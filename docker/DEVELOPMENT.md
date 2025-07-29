# Docker Development Guide

## Running Commands in Docker

Always run Node.js/npm commands **inside the Docker container** to avoid architecture conflicts.

### Quick Commands

```bash
# Using make commands (recommended)
make dev              # Run npm dev server
make shell            # Access container shell
make install-deps     # Install all dependencies
make fix-node-modules # Fix ARM64 issues

# Using the sail wrapper
./sail npm run dev
./sail artisan migrate
./sail composer install
./sail npm install
```

### Common Issues

#### ARM64/M1/M2 Mac Issues

If you see errors about missing `@rollup/rollup-linux-arm64-gnu`:

```bash
# Fix it with:
make fix-node-modules

# Or manually:
rm -rf node_modules package-lock.json
docker compose exec laravel.test npm install
```

#### Running Development Server

**❌ DON'T run on host:**
```bash
npm run dev  # This will cause architecture issues
```

**✅ DO run in container:**
```bash
make dev                          # Easiest way
# or
docker compose exec laravel.test npm run dev
# or
./sail npm run dev
```

### Best Practices

1. **Always install dependencies in the container:**
   ```bash
   make install-deps
   ```

2. **Use the sail wrapper for quick commands:**
   ```bash
   chmod +x sail
   ./sail artisan tinker
   ./sail composer require package/name
   ```

3. **For long development sessions, use shell:**
   ```bash
   make shell
   # Then inside container:
   npm run dev
   ```

4. **If switching between host and container development:**
   - Always remove `node_modules` when switching
   - Reinstall dependencies in the new environment

### Available Make Commands

Run `make help` to see all available commands:

- `make dev` - Run development server
- `make shell` - Access Laravel container
- `make install-deps` - Install all dependencies
- `make fix-node-modules` - Fix ARM64 node issues
- `make logs` - View container logs
- `make down` - Stop containers
- `make up` - Start containers

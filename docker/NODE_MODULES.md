# Node Modules Isolation Strategy

## Overview

This project uses **native architecture builds** with proper isolation between host and container environments. This gives you:

- ✅ **Native performance** on ARM64 (M1/M2) Macs
- ✅ **No architecture conflicts** between host and container
- ✅ **Faster builds and execution**

## How It Works

### 1. Volume Isolation

The `docker-compose.yml` includes this critical line:
```yaml
volumes:
  - ./:/var/www/html:delegated
  - /var/www/html/node_modules  # This line prevents conflicts
```

This creates an **anonymous volume** for `node_modules` that:
- Stays inside the container
- Doesn't sync with your host
- Uses the correct architecture for the container

### 2. .dockerignore

The `.dockerignore` file prevents copying host `node_modules` during build:
```
node_modules/
```

### 3. Architecture Detection

Both Dockerfiles detect and use the appropriate architecture:
- ARM64 for M1/M2 Macs
- AMD64 for Intel machines

## Development Workflow

### ✅ DO: Run all npm commands in the container

```bash
# Good approaches:
make dev                           # Recommended
./sail npm run dev                 # Also good
docker compose exec laravel.test npm run dev  # Direct approach
```

### ❌ DON'T: Run npm commands on your host

```bash
# These will cause architecture conflicts:
npm install     # Don't do this
npm run dev     # Don't do this
```

### Installing New Packages

Always install packages inside the container:
```bash
./sail npm install package-name
# or
make shell
npm install package-name
```

## Benefits

1. **No Rosetta overhead** - Native ARM64 performance on M1/M2
2. **Clean separation** - Host and container environments don't conflict
3. **Consistent behavior** - Works the same for all team members
4. **Fast rebuilds** - No need to rebuild when switching contexts

## Troubleshooting

### If you accidentally ran npm on your host:

1. Remove the host node_modules:
   ```bash
   rm -rf node_modules
   ```

2. The container already has the correct node_modules, so just continue:
   ```bash
   make dev
   ```

### To view what's in the container's node_modules:

```bash
./sail ls -la node_modules/
```

### To reinstall dependencies:

```bash
./sail npm ci  # Faster, uses package-lock.json
# or
./sail npm install  # Full reinstall
```

## Why This Approach?

- **Performance**: Native architecture = no emulation overhead
- **Isolation**: Container node_modules never conflicts with host
- **Simplicity**: No complex workarounds or architecture forcing
- **Reliability**: Each environment uses its native architecture

This is the Docker best practice for handling node_modules in development!

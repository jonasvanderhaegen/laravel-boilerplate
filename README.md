[![Tests](https://github.com/jonasvanderhaegen/laravel-boilerplate/actions/workflows/tests.yml/badge.svg)](https://github.com/jonasvanderhaegen/laravel-boilerplate/actions/workflows/tests.yml)
![Version - alpha](https://img.shields.io/badge/Version-alpha-blue) [![Dockerized Setup](https://img.shields.io/badge/Dockerized%20Setup-Ready-blue?logo=docker\&logoColor=white)](https://docs.docker.com/get-docker/)

### 🧭 Initialization

Docker-only setup for new developers (Just have docker installed)<br>
TLDR: Clone repository, Set up local environment. [For more details read here](docs/start/init.md) <br>
FYI: An alias is like a shortcut or nickname you create for a longer command you don’t want to type over and over again.

🪟 **On Windows** (CMD, Git Bash, or PowerShell):
```bash
./scripts/sail.bat
```

🍎 **On macOS / Linux / WSL:**
  ```bash
./scripts/sail.sh
```

Source the target file as mentioned in terminal or close and re-open terminal in case it or you added aliases to your terminal.<br>
Then you can run the following, it will compile frontend assets, run queue worker in background, etc. 

```bash
# Compile frontend assets with vite, run queue worker, etc
./vendor/bin/sail composer run dev
# or
sail composer run dev
sr
```
CTRL + X to close this process

---

🛳️ Optional: [Create a sail alias (recommended)](docs/start/alias.md) to avoid typing ./vendor/bin/sail every time. <br>
With initialization for mac users this was prompted. If you said yes it's already done for you.

```bash
s     = sail
sc    = sail composer
sa    = sail artisan
sm    = sail artisan migrate
smf   = sail artisan migrate:fresh
smfs  = sail artisan migrate:fresh --seed

# Start docker container if down
sup   = sail up -d

# Stop docker containerr
sus   = sail stop

# Start process of vite
sr    = sail composer run dev
```
---

### 🧭 Docker

In case you already initialized project

```bash
# Docker container's down? Start container like this in the background

./vendor/bin/sail up -d
# or with alias
sail up -d
sup
```

```bash
# Want to bring docker container's down? Stop container like this

./vendor/bin/sail stop
# or with alias
sail down
sus
```

```bash
# Compile frontend assets with vite, run queue worker, etc
./vendor/bin/sail composer run dev
# or with alias
sail composer run dev
sc run dev
sr
```

---

#### Connections with GUI
When docker container is running you can connect to PGSQL, Valkey (Redis) with a database client GUI.

---

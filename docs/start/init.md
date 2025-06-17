[![Dockerized Setup](https://img.shields.io/badge/Dockerized%20Setup-Ready-blue?logo=docker\&logoColor=white)](https://docs.docker.com/get-docker/)

## 🧭 Getting Started

Clone the repository and launch the project with **zero local PHP/Node dependencies** — only **Docker** is required.

> \[!TIP]
> 💡 **Docker-only setup for new developers**
> If you're a developer who **does not want to install PHP, Composer, Node, or NPM locally**, and prefers to run everything inside Docker:

🪟 **On Windows** (CMD, Git Bash, or PowerShell):
```bash
./scripts/sail.bat
```

🍎 **On macOS / Linux / WSL:**
  ```bash
./scripts/sail.sh
```

This will:

* Copy `.env` from `.env.example` if missing
* Run `composer install` inside Docker
* Start Laravel Sail containers
* Run migrations and optionally seeders
* Install and compile frontend assets (if `package.json` is present)
* Asks and installs alias for sail

✅ No local setup required beyond **Docker**.

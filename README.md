## [![Tests](https://github.com/jonasvanderhaegen/laravel-boilerplate/actions/workflows/tests.yml/badge.svg)](https://github.com/jonasvanderhaegen/laravel-boilerplate/actions/workflows/tests.yml) ![Version](https://img.shields.io/badge/Version-alpha-blue) [![Docker Ready](https://img.shields.io/badge/Docker-ready-blue?logo=docker\&logoColor=white)](https://docs.docker.com/get-docker/)

# Laravel Boilerplate

## 🔒 HTTPS Support

This boilerplate includes full HTTPS support with trusted SSL certificates for local development.

### Quick Start with HTTPS

```bash
# One-command setup with custom domain
chmod +x setup-https.sh && ./setup-https.sh

# Or use make
make fresh-start DOMAIN=myapp.test
```

**Note:** The setup scripts automatically detect whether to use `docker compose` or `docker-compose` based on your Docker installation.

Your app will be available at `https://myapp.test` with a green padlock! 🎉

For detailed SSL setup instructions, see [docker/SSL-README.md](docker/SSL-README.md).

**Important:** Node modules are isolated inside containers. See [docker/NODE_MODULES.md](docker/NODE_MODULES.md) for details.

### Development Commands

**Important:** Always run npm/node commands inside the Docker container to avoid architecture issues.

```bash
# Run development server
make dev

# Access container shell
make shell

# Install dependencies
make install-deps

# Fix ARM64/M1/M2 node issues
make fix-node-modules
```

For more details, see [docker/DEVELOPMENT.md](docker/DEVELOPMENT.md).

An opinionated Laravel starter kit tailored for rapid development, built by Jonas Vanderhaegen. Includes Docker-based setup, Livewire SPA scaffolding, modular architecture, and more.

## Project-Specific Instructions for AI Assistants

When working with this codebase, AI assistants should:

1. **Check these files first**:
    - `.cursorrules` - IDE-specific rules and project conventions
    - `.ai-instructions.md` - Detailed AI assistant guidelines
    - `README.md` - This file, for project overview
    - Module-specific README files in `Modules/*/README.md`

2. **Follow the modular architecture**:
    - Each feature should be contained within a module
    - Modules are located in the `Modules/` directory
    - Use `php artisan module:*` commands for module operations

3. **Maintain consistency**:
    - Follow existing code patterns and conventions
    - Use the same libraries and tools already in the project
    - Check similar implementations before creating new code

## Important Directories
- `/Modules` - Application modules
- `/app` - Core application code
- `/config` - Configuration files
- `/database` - Migrations and seeders
- `/tests` - Test files
- `/resources` - Views and frontend assets

## Coding Standards
This project follows:
- PSR-12 for PHP code style
- Laravel naming conventions
- TypeScript for type safety
- Tailwind CSS for styling

For detailed guidelines, see `.cursorrules` and `.ai-instructions.md`.


> [!IMPORTANT]
> **Note:** This boilerplate reflects my personal preferences and workflow. Pull requests are welcome, though changes may be opinionated.<br>
> For mid to large size projects I just tend to work more modular than all together.

---

## Features

* **Docker-Only Setup**: Run without installing PHP, Composer, Node, or NPM locally.
* **Livewire SPA**: Single-page application powered by Laravel Livewire and Flowbite.
* **Modular Architecture**: Built-in support for modules (*Core*, Auth, Onboarding, etc.).
* **Custom Scripts**: One-command installation & environment setup.
* **GitHub Actions**: CI for tests, type coverage, and performance optimized workflows.
* **Modern Services**: Postgres, Valkey (Redis replacement), Mailpit for dev email.

---

## Prerequisites

* Docker & Docker Compose installed. [Get Docker](https://docs.docker.com/get-docker/)
* (Optional) Git client

---

## Quick Start

1. **Fork and clone the repository to local environment**

Example
```bash
  git clone git@github.com:jonasvanderhaegen/laravel-boilerplate.git project
  cd mroject
```

2. **Run the installer script**

> [!TIP]
> please keep your attention to terminal because there'll be some offering prompts.

* **macOS / Linux / WSL**

```bash
  ./scripts/sail.sh
```
* **Windows (CMD/PowerShell/Git Bash)**

```bash
  ./scripts/sail.bat
```

The script will:

  1. Set up docker container with services php 8.4, composer, valkey, mailpit, postgres SQL [and more services can be added by yourself](https://laravel.com/docs/master/sail)
  2. Copy `.env.example` → `.env`
  3. Install Composer & NPM dependencies 
  4. Generate `APP_KEY`
  5. Optionally run database migrations & seeders 
  6. Configure Sail aliases

 ```bash
   # Example aliases added to your shell:
  alias s='sail'
  alias sc='sail composer'
  alias sa='sail artisan'
  alias smf='sail artisan migrate:fresh --seed'
  alias sup='sail up -d'
  alias sus='sail stop'
  alias sr='sail composer run dev'
 ```

3. **Access the application**

    * Frontend: `http://localhost`
    * Mailpit: `http://localhost:8025`

---

## Aliases & Scripts

#### Sail commands for Beginners

| Alias | Command                             | Description                       |
|-------|-------------------------------------|-----------------------------------|
| `s`   | `sail`                              | Wrapper for Sail commands         |
| `sc`  | `sail composer`                     | Run Composer inside the container |
| `sa`  | `sail artisan`                      | Run Artisan inside the container  |
| `sm`  | `sail artisan migrate`              | Run migrations                    |
| `smf` | `sail artisan migrate:fresh --seed` | Reset database and seed           |
| `sup` | `sail up -d`                        | Start containers in detached mode |
| `sus` | `sail stop`                         | Stop containers                   |
| `sr`  | `sail composer run dev`             | Build frontend assets             |


#### Explaining the Composer Scripts for Beginners

| Script                  | Description                                                                                                                                  |
|-------------------------|----------------------------------------------------------------------------------------------------------------------------------------------|
| `sc rector`             | Applies automated code refactoring rules to modernize or fix your PHP codebase using the [Rector tool](https://github.com/rectorphp/rector). |
| `sc lint`               | Runs Laravel Pint to check and automatically fix PHP code style issues according to the configured Pint rules.                               |
| `sc test:rector`        | Performs a dry run of Rector, showing which changes *would* be made without modifying any files.                                             |
| `sc test:lint`          | Executes Pint in test mode to report any style violations without applying fixes.                                                            |
| `sc test:types`         | Runs PHPStan static analysis to detect type errors and potential bugs using the `phpstan.neon` configuration.                                |
| `sc test:unit`          | Executes unit tests with Pest—running in parallel, showing colors, and enforcing 100% code coverage.                                         |
| `sc test:type-coverage` | Measures type coverage with Pest, ensuring every function and method has type annotations and checks.                                        |
| `sc test`               | Composite command that does all above together                                                                                               |

#### Explaining added module custom artisan commands for Beginners

| Script                  | Short  |                                                                                      Description                                                                                      |
|-------------------------|--------|:-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------:|
| sail artisan core:reset | sa c:r | migrate:fresh --seed, module:seed with cache cleared. It does not seed for modules with migrate:fresh<br> You can add whatever to this command, for example artisan scout:import, etc |

---

## Modules & Roadmap

* **Core**: Rate limiters, mobile/desktop detection, base user features.
* **Auth**: Classic pages (login, register, forgot/reset), email verification.
* **Onboarding** (coming soon)
* **Profile Settings** (coming soon)
* **WebAuthn**: Passkeys support (planned)
* **Sharding**: Horizontal database scaling (planned)
* **IDE helper files**: For Visual Studio Code, Submit 3, PhpStorm

Contributions and suggestions are welcome! See [Contributing](#contributing).

---

## Contributing

1. Fork the repository
2. Create a feature branch: `git checkout -b feature/name`
3. Commit changes: `git commit -m "Add new feature"`
4. Push branch: `git push origin feature/name`
5. Open a Pull Request

Please adhere to PSR, follow Laravel conventions, and include tests where applicable.

---

## License

This project is licensed under the MIT License. See the [LICENSE](https://opensource.org/licenses/MIT) for details.

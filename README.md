## [![Tests](https://github.com/jonasvanderhaegen/laravel-boilerplate/actions/workflows/tests.yml/badge.svg)](https://github.com/jonasvanderhaegen/laravel-boilerplate/actions/workflows/tests.yml) ![Version](https://img.shields.io/badge/Version-alpha-blue) [![Docker Ready](https://img.shields.io/badge/Docker-ready-blue?logo=docker\&logoColor=white)](https://docs.docker.com/get-docker/)

# Laravel Boilerplate

An opinionated Laravel starter kit tailored for rapid development, built by Jonas Vanderhaegen. Includes Docker-based setup, Livewire SPA scaffolding, modular architecture, and more.

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

    * Set up docker container with services php 8.4, composer, valkey, mailpit, postgres SQL [and more services can be added by yourself](https://laravel.com/docs/master/sail)
    * Copy `.env.example` → `.env`
    * Install Composer & NPM dependencies
    * Generate `APP_KEY`
    * Optionally run database migrations & seeders
    * Configure Sail aliases

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

### Sail commands for Beginners

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


### Explaining the Composer Scripts for Beginners

| Script               | Description                                                                                                                                  |
|----------------------|----------------------------------------------------------------------------------------------------------------------------------------------|
| `rector`             | Applies automated code refactoring rules to modernize or fix your PHP codebase using the [Rector tool](https://github.com/rectorphp/rector). |
| `lint`               | Runs Laravel Pint to check and automatically fix PHP code style issues according to the configured Pint rules.                               |
| `test:rector`        | Performs a dry run of Rector, showing which changes *would* be made without modifying any files.                                             |
| `test:lint`          | Executes Pint in test mode to report any style violations without applying fixes.                                                            |
| `test:types`         | Runs PHPStan static analysis to detect type errors and potential bugs using the `phpstan.neon` configuration.                                |
| `test:unit`          | Executes unit tests with Pest—running in parallel, showing colors, and enforcing 100% code coverage.                                         |
| `test:type-coverage` | Measures type coverage with Pest, ensuring every function and method has type annotations and checks.                                        |
| `test`               | Composite command that does all above together                                                                                               |

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

# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## About

PrestaShop Checkout is the official PrestaShop payment module in partnership with PayPal. The `main` branch supports v5 for PrestaShop 1.7, 8, and 9 simultaneously. Older versions use dedicated maintenance branches (`prestashop/9.x`, `prestashop/8.x`, `prestashop/1.7.x`).

## Development Setup

Requirements: PHP, Composer, Docker, Docker Compose, GNU Make.

```bash
cp .env.dist .env          # Configure PS_VERSION_TAG, MODULE_VERSION, etc.
cp <MODULE_VERSION>/.env.dist <MODULE_VERSION>/.env
cp docker-compose.local.yml.dist docker-compose.local.yml
make build                 # Build Docker images
make up                    # Start containers + install root dependencies
```

The shop runs at `https://<PS_DOMAIN>` via Cloudflare tunnel, or `http://localhost:8991` directly (admin: `demo@prestashop.com` / `prestashop_demo`).

`make up` auto-generates `.cloudflared.yml` from `.cloudflared.yml.dist` — do not edit it directly.

Key `.env` variables:
- `MODULE_VERSION`: `ps17`, `ps8`, or `ps9` — controls which PrestaShop container to target
- `PS_VERSION_TAG`: Docker image tag for the PrestaShop version (e.g., `8`)
- `PS_DOMAIN`: public shop domain used by Cloudflare tunnel and PrestaShop's shop URL
- `CLOUDFLARED_DOMAIN`: base domain for tunnel subdomains (`logs.`, `glitchtip.`)
- `TUNNEL_ID`: Cloudflare tunnel UUID — run `cloudflared tunnel list` to find it
- `CLOUDFLARED_CREDENTIALS_FILE`: absolute path to the tunnel credentials JSON
- `SENTRY_DSN`: Glitchtip/Sentry DSN for error monitoring

## Commands

```bash
make lint                  # php-cs-fixer + autoindex
make php-cs-fixer          # Fix code style only
make phpstan               # PHPStan static analysis (uses $MODULE_VERSION), must be run only by developers
make phpstan-baseline      # Regenerate PHPStan baseline for current $MODULE_VERSION, must be run only by developers
make phpstan-baseline-all  # Regenerate baselines for all versions, must be run only by developers

make unit-test             # All unit tests (api, utility, core, presentation)
make integration-test      # All integration tests (module, core, infrastructure), must be run only by developers
make test                  # Full test suite

# Run a single test suite directly
make php-unit-core
make php-unit-api
make php-unit-utility
make php-unit-presentation
make php-integration # must be run only by developers
make php-integration-core # must be run only by developers
make php-integration-infrastructure # must be run only by developers

make ssh                   # Shell into the running PrestaShop container
make install-module        # Install the module in PrestaShop via console
```

Module logs (inside project root, not container): `prestashop/<PS_VERSION_TAG>/var/logs/ps_checkout-*-<YYYY-MM-DD>`.

All `make` test commands run inside the Docker container using `$MODULE_VERSION` and `$PS_VERSION_TAG` from `.env`.
All `make *-test` commands require a running Docker container (`make up`).

Do not run `phpunit` directly from the host — cross-package autoloading (e.g., `api/` classes in `core/` tests) only resolves inside the Docker container via the module's `vendor/autoload.php`. Always use `make` commands for tests.

Code style check without fixing: `composer cs` (from root).

## Architecture

### Multi-version monorepo

The repository targets three PrestaShop versions using a monorepo approach:

```
ps17/   — PS 1.7 module shell (PHP ^7.1)
ps8/    — PS 8.x module shell (PHP ^7.2|^8.1)
ps9/    — PS 9.x module shell (PHP ^8.1, Symfony 6.4)
```

Each version directory contains:
- `ps_checkout.php` — main module entry point extending `PaymentModule`
- `sentry.php` — Sentry/Glitchtip error monitoring initialization
- `config/` — Symfony service container YAML definitions
- `tests/` — module-level integration tests
- `composer.json` — version-specific dependencies

The version-specific directories are thin shells. All business logic lives in the shared monorepo packages below.

### Shared packages (vendor/invertus/)

The monorepo packages are in the root alongside `ps17/`, `ps8/`, `ps9/` and symlinked/mounted into each version's `vendor/invertus/`:

| Package | Namespace | Responsibility |
|---|---|---|
| `api/` | `PsCheckout\Api\` | HTTP controllers, DTOs, value objects |
| `core/` | `PsCheckout\Core\` | Business logic: orders, webhooks, PayPal, settings, payment tokens |
| `infrastructure/` | `PsCheckout\Infrastructure\` | PrestaShop adapters, repositories, controllers, loggers |
| `presentation/` | `PsCheckout\Presentation\` | Presenters for front/back-office UI |
| `utility/` | `PsCheckout\Utility\` | Shared array/string/payload utilities |

The `ps<version>/src/` directory (namespace `PsCheckout\Module\`) contains only version-specific presentation overrides.

### Key domain areas in `core/`

- `PayPal/` — PayPal API integration (orders, payments, webhooks, funding sources, refunds, Apple/Google Pay)
- `Order/` + `OrderState/` — Order processing and status transitions
- `Webhook/` + `WebhookDispatcher/` — Webhook event handling pipeline
- `Settings/` — Configuration management
- `PaymentToken/` — Payment token lifecycle

### Key areas in `infrastructure/`

- `Adapter/` — Bridge between the core domain and PrestaShop's legacy APIs
- `Repository/` — Database access layer
- `Bootstrap/` — Module installation/upgrade logic
- `Controller/` — Web controllers (front + admin)
- `Environment/` — Runtime environment detection

### CI/CD (`.github/workflows/`)

- `ci.yml` — runs tests on pull requests
- `lint.yml` — runs linting checks
- `create-testing-zip.yml` — generates module ZIP for testing
- `prerelease.yml` — pre-release pipeline
- `publish-to-marketplace.yml` — publishes to PrestaShop Marketplace

### PHPStan workflow

Must be run by developers locally before merging code.

`make phpstan` runs locally (no Docker needed).
When modifying shared packages, always run `make phpstan` and fix reported errors.
Only regenerate the baseline (`make phpstan-baseline`) if errors are pre-existing or unfixable (e.g., baseline count mismatches from removed code).

- **Test files**: PHPStan requires explicit return types on test methods (`: void`), `@return` annotations on data providers (e.g., `@return array<string, array{int, string}>`), and `@var` type hints when accessing nested array results from `handle()` methods.
- `make phpstan` only checks the version set in `$MODULE_VERSION`. To verify all versions, also run `composer phpstan` from within `ps9/` and `ps17/` (or whichever versions aren't the default).
- PHPStan baselines (`ps8/phpstan-baseline.neon`, `ps9/phpstan-baseline.neon`, `ps17/phpstan-baseline.neon`) have identical entries for shared `core/` test files — when updating baseline counts, apply the same change to all three.

### PHP-CS-Fixer scope

PHP-CS-Fixer applies to: `api/`, `core/`, `infrastructure/`, `presentation/`, `utility/` — not to the `ps17/`, `ps8/`, `ps9/` version directories. Rules: PSR-2, AFL-3.0 header comment, no unused imports.

### Service container (admin vs front)

Each version (`ps{8,9,17}/config/`) has separate service containers for admin and front contexts. A service registered in `config/front/*.yml` is **not** available in admin controllers, and vice versa. If an admin controller calls `$this->module->getService(Foo::class)` and gets `ServiceNotFoundException`, check whether `Foo` is only registered in `config/front/` and needs an entry in `config/admin/` too.

### Coding standards

Follow [PrestaShop coding standards](https://devdocs.prestashop-project.org/8/development/coding-standards/). Do not update the module version number in pull requests.

- Every new directory must contain an `index.php` file (security requirement). Copy from any existing `index.php` in `core/tests/`. `make lint` runs autoindex which creates missing ones, but adding them upfront keeps commits clean.

### Admin AJAX controller patterns

- **Response method**: Use `exitWithResponse(['httpCode' => N, 'status' => bool, ...])` for all JSON responses — not `http_response_code()` + `ajaxRender(json_encode(...))`. No `return` needed; `exitWithResponse` exits internally.
- **Request encoding**: Admin AJAX calls must POST with `Content-Type: application/x-www-form-urlencoded` so `Tools::getValue('action')` can route to the correct `ajaxProcess*` method. `application/json` bodies are invisible to `Tools::getValue`.

### Back-office JS pattern (`views/js/`)

- `views/js/<name>.js` — defines `var ps_checkout_<name> = {}` with an `initialize(config)` method (never inline in a template)
- `views/templates/hook/partials/<name>.tpl` — thin wrapper: guard with `typeof ps_checkout_<name> !== 'undefined'`, then call `.initialize({...})` with Smarty-escaped config values
- Register in `hookActionAdminControllerSetMedia` via `addJS(...'?version=' . $this->version . '&rand=' . time(), false)`
- JS files under `ps{8,9,17}/views/js/` are identical across versions — create one, `cp` to the other two
- Admin controllers (`ps{8,9,17}/controllers/admin/`) are nearly identical across versions — apply the same edit to all three. Exception: ps9 uses `\Exception` (namespaced), ps8/ps17 use `Exception` (global).
- **Logging in catch blocks**: Always add local logging via `$this->module->getService(LoggerInterface::class)->error(...)` alongside `\Sentry\captureException()`. Sentry alone is insufficient — it may be unconfigured in dev/staging.
- **Admin error messages**: Default/fallback error messages in admin catch blocks should append `$exception->getMessage()` for merchant debugging context. Customer-facing defaults should remain generic.

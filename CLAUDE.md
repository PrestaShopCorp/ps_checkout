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

The shop runs at `http://localhost:8991` (admin: `demo@prestashop.com` / `prestashop_demo`).

Key `.env` variables:
- `MODULE_VERSION`: `ps17`, `ps8`, or `ps9` — controls which PrestaShop container to target
- `PS_VERSION_TAG`: Docker image tag for the PrestaShop version (e.g., `8`)
- `SENTRY_DSN`: Glitchtip/Sentry DSN for error monitoring

## Commands

```bash
make lint                  # php-cs-fixer + autoindex
make php-cs-fixer          # Fix code style only
make phpstan               # PHPStan static analysis (uses $MODULE_VERSION)
make phpstan-baseline      # Regenerate PHPStan baseline for current $MODULE_VERSION
make phpstan-baseline-all  # Regenerate baselines for all versions

make unit-test             # All unit tests (api, utility, core, presentation)
make integration-test      # All integration tests (module, core, infrastructure)
make test                  # Full test suite

# Run a single test suite directly
make php-unit-core
make php-unit-api
make php-unit-utility
make php-unit-presentation
make php-integration
make php-integration-core
make php-integration-infrastructure

make ssh                   # Shell into the running PrestaShop container
make install-module        # Install the module in PrestaShop via console
```

All `make` test commands run inside the Docker container using `$MODULE_VERSION` and `$PS_VERSION_TAG` from `.env`.

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

- `PayPal/` — PayPal API integration (14+ subdirectories covering orders, payments, webhooks, funding sources)
- `Order/` + `OrderState/` — Order processing and status transitions
- `Webhook/` + `WebhookDispatcher/` — Webhook event handling pipeline
- `Settings/` — Configuration management
- `PaymentToken/` — Payment token lifecycle

### Key areas in `infrastructure/`

- `Adapter/` (29 adapters) — Bridge between the core domain and PrestaShop's legacy APIs
- `Repository/` (43+ repositories) — Database access layer
- `Bootstrap/` — Module installation/upgrade logic
- `Controller/` — Web controllers (front + admin)
- `Environment/` — Runtime environment detection

### PHP-CS-Fixer scope

PHP-CS-Fixer applies to: `api/`, `core/`, `infrastructure/`, `presentation/`, `utility/` — not to the `ps17/`, `ps8/`, `ps9/` version directories. Rules: PSR-2, AFL-3.0 header comment, no unused imports.

### Merchant SDK (Admin Order View) — PAYSHIP-3878

The Merchant SDK is a **zoid** component loaded on the admin order view page. It exposes `window.PrestaShopCheckoutSDK.PrestaShopCheckout(props)`, which takes `{ orderData, transactionList, onSubmit }` and renders into a container via `.render('#id')`.

**Key files:**
- `core/src/Settings/Configuration/PayPalSdkConfiguration.php` — `SDK_MERCHANT_ENDPOINT` constant
- `ps{17,8,9}/.env.dist` — `CHECKOUT_MERCHANT_SDK_URL` / `CHECKOUT_MERCHANT_SDK_VERSION` vars
- `ps{17,8,9}/ps_checkout.php` — `case 'AdminOrders':` loads the SDK; hooks inject `window.store.context` with `orderId` + `prestashopCheckoutAjax`
- `ps{17,8,9}/views/templates/hook/displayAdminOrderMainBottom.tpl` — container div + partial include
- `ps17/views/templates/hook/displayAdminOrderLeft.tpl` — same (pre-1.7.7 hook)
- `ps{17,8,9}/views/templates/hook/partials/adminOrderViewSdk.tpl` — initialization script

**`onSubmit` action → AJAX endpoint mapping:**
- `capture` → `CaptureAuthorization`
- `void` → `VoidAuthorization`
- `reauthorize` → `ReauthorizeAuthorization`
- `refund` → `RefundOrder`

**Local dev:** set `CHECKOUT_MERCHANT_SDK_URL=http://localhost:5174/merchant-sdk.umd.js` in `.env`. The URL ends in `.js` so the PHP version-appending logic is skipped.

**Current state (as of PAYSHIP-3878):** placeholder `orderData` and `transactionList` are hardcoded in the template. Real data mapping from PHP presenters is the follow-up task.

### Coding standards

Follow [PrestaShop coding standards](https://devdocs.prestashop-project.org/8/development/coding-standards/). Do not update the module version number in pull requests.

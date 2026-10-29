# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/).

---

## [Unreleased]

### Added

- **XO-3014** — Handle 422 MDU registration error on order creation
  - Detect `SHOP_NOT_REGISTERED_IN_MDU` error code returned by `order-api` with HTTP 422
  - Display a polite, generic message to the buyer: *"This payment method is temporarily unavailable, please choose another one."*
  - Log an explicit error message on the merchant side when the shop is not registered in the PrestaShop Checkout services
  - Persist a `PS_CHECKOUT_SHOP_NOT_REGISTERED_IN_MDU` configuration flag when the error occurs
  - Display a persistent danger alert on the module configuration page in the back-office when the flag is set
  - Translations added for the admin alert in: English, French, German, Spanish, Italian, Dutch, Polish, Portuguese
  - New `PsCheckoutException::SHOP_NOT_REGISTERED_IN_MDU` exception code (`82`)

### Fixed

- **XO-3131** — Validate the vault ID ownership before creating the PayPal order
  - The check that the vault ID belongs to the current customer now runs before any call to PayPal, instead of after the order creation
  - A vault ID that is unknown, belongs to another PayPal customer, or is sent by a guest / customer without PayPal customer ID is rejected without creating a PayPal order
- **XO-3137** — Mask vault IDs in module and HTTP logs
  - HTTP request and response bodies are now scrubbed by `SensitiveDataProcessor` (they were logged as raw JSON strings)
  - `vault_id`, `vaultId`, `customer_id` and the `id` / `customer.id` of a `vault` node are masked, keeping the last 4 characters
  - The vault ID segment of payment-token URLs is masked
- Integration tests: create a fresh `test_prestashop` database automatically before each run (`make create-test-db`)
- Integration tests: add `stderr="true"` to `phpunit-integration.xml` to prevent "headers already sent" errors caused by PHPUnit output conflicting with PHP session start

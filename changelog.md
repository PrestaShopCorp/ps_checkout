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

- Integration tests: create a fresh `test_prestashop` database automatically before each run (`make create-test-db`)
- Integration tests: add `stderr="true"` to `phpunit-integration.xml` to prevent "headers already sent" errors caused by PHPUnit output conflicting with PHP session start

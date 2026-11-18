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
- **XO-3132** — Do not expose visitor-specific data in the HTML of cacheable pages
  - Saved payment methods (vault ID and label), the PayPal order ID of the cart, the express checkout flag and the cart product count are now only injected on the checkout pages (`order`, `order-opc`)
  - Product, cart and other pages no longer leak these values to other visitors when served from a full-page cache
- **XO-3137** — Mask vault IDs in module and HTTP logs
  - HTTP request and response bodies are now scrubbed by `SensitiveDataProcessor` (they were logged as raw JSON strings)
  - `vault_id`, `vaultId`, `customer_id`, the `id` / `customer.id` of a `vault` node and `attributes.customer.id` are masked, keeping the last 4 characters
  - The vault ID segment of payment-token URLs is masked
- **XO-3174** — Use the card 3D Secure result again when validating a payment (regression since v5.3.2)
  - `PayPalOrderResponse::getAuthenticationResult()` always returned `null`, so the 3D Secure decision was always "no decision"
  - With `SCA_ALWAYS`, every card payment was rejected, even after a successful 3D Secure
  - With `SCA_WHEN_REQUIRED`, payments with a failed (`NO` liability shift + `N`/`R`) 3D Secure are rejected again, and `UNKNOWN` results ask the customer to retry
  - The `3DS_NOT_REQUIRED` tag is only added again to orders that really had no 3D Secure. Orders saved since v5.3.2 with `SCA_WHEN_REQUIRED` keep a wrong tag
  - Log a warning when a `CHECKOUT.ORDER.APPROVED` webhook does not capture because the order validation failed
  - Log a warning in the `payment` front controller when the order is not captured after the 3D Secure step (failed 3D Secure, no 3D Secure result with `SCA_ALWAYS`, retry without a `payer-action` link)
- Integration tests: create a fresh `test_prestashop` database automatically before each run (`make create-test-db`)
- Integration tests: add `stderr="true"` to `phpunit-integration.xml` to prevent "headers already sent" errors caused by PHPUnit output conflicting with PHP session start

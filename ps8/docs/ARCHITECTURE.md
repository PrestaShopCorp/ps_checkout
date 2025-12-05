PrestaShop 8 — Version‑specific Architecture Notes

This document complements the root `ARCHITECTURE.md`. It focuses on what is specific to the PrestaShop 8 wrapper under `ps8/`.

- Module class
  - `ps8/ps_checkout.php` (e.g., version 8.5.1.0)

- Hooks
  - Declares `HOOK_LIST` including payment hooks, shipping/order updates, `moduleRoutes`, etc.

- Front‑office controllers (examples)
  - `ps8/controllers/front/ExpressCheckout.php`
  - `ps8/controllers/front/webhook.php`

- Apple Pay
  - Domain association file at `ps8/.well-known/apple-LIVE-merchantid-domain-association`.

- Tests
  - `ps8/tests/Integration/Controller/WebhookControllerTest.php` demonstrates webhook behaviors.

- Notes
  - Leverages PS 8 service container and presenter patterns aligned with PS 8 controller/hook behaviors.
  - Shared layers (core, api, infrastructure, presentation) are identical across versions; only the wrapper differs.

See the root `ARCHITECTURE.md` for shared architecture, component interactions, and data flow diagrams.

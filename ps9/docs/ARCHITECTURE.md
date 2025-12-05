PrestaShop 9 — Version‑specific Architecture Notes

This document complements the root `ARCHITECTURE.md`. It focuses on what is specific to the PrestaShop 9 wrapper under `ps9/`.

- Module class
  - `ps9/ps_checkout.php` (e.g., version 9.5.1.0)

- Hooks
  - Aligned with PS 9 platform events; similar `HOOK_LIST` to PS 8.

- Front‑office controllers (examples)
  - `ps9/controllers/front/ExpressCheckout.php`
  - `ps9/controllers/front/webhook.php`

- Notes
  - Wrapper keeps parity with PS 9 routing and BO integration; the shared layers remain identical.
  - Shared layers (core, api, infrastructure, presentation) are identical across versions; only the wrapper differs.

See the root `ARCHITECTURE.md` for shared architecture, component interactions, and data flow diagrams.

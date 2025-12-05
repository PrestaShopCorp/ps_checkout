PrestaShop 1.7 — Version‑specific Architecture Notes

This document complements the root `ARCHITECTURE.md`. It focuses on what is specific to the PrestaShop 1.7 wrapper under `ps17/`.

- Module class
  - `ps17/ps_checkout.php` (e.g., version 7.5.1.0)

- Hooks
  - Similar to PS 8/9, with additions like `displayAdminOrderLeft` present in the 1.7 wrapper.

- Front‑office controllers (examples)
  - `ps17/controllers/front/ExpressCheckout.php`
  - `ps17/controllers/front/webhook.php`

- Admin integration
  - Installs an invisible admin tab `AdminAjaxPrestashopCheckout` used for internal AJAX endpoints.

- Notes
  - UI integration points (template locations) and some BO controller behaviors follow PS 1.7 conventions.
  - Shared layers (core, api, infrastructure, presentation) are identical across versions; only the wrapper differs.

See the root `ARCHITECTURE.md` for shared architecture, component interactions, and data flow diagrams.

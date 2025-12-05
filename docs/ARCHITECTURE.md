PrestaShop Checkout - System Architecture

This document describes the system architecture of the module, component interactions, key data flows, design decisions, constraints, and version-specific notes for PrestaShop 1.7, 8, and 9.

Top-level structure:
- api/ - HTTP clients and configuration builders to communicate with PayPal (Orders, Payments, Webhooks, Shipment tracking)
- core/ - Business/domain logic: processors, actions, validators, webhook handlers
- infrastructure/ - Adapters for PrestaShop context, repositories, controller base, installer/bootstrap
- presentation/ - Presenters and translators for BO/FO UIs
- ps17/, ps8/, ps9/ - Version-specific wrappers (module class, controllers, hooks, routing)


1. High-level overview

PrestaShop Checkout integrates PayPal and related funding sources (PayPal, Card Fields, Google Pay, Apple Pay) into PrestaShop. The module follows a layered architecture:

- The version wrapper (e.g., ps8/ps_checkout.php) integrates with PrestaShop through hooks and routes, and delegates work to services from the core layer using a service container.
- The core layer encapsulates domain logic: order processing, order state management, validations, webhook handling, and PayPal order/providers.
- The api layer contains PSR-based HTTP clients with configuration builders to call PayPal APIs and normalize errors.
- The infrastructure layer adapts the domain to PrestaShop (context, repositories, installers) and provides a base controller for FO endpoints.
- The presentation layer prepares data structures for BO/FO templates through presenters and translators.

Runtime flow:
- Hooks and FO controllers in the version wrapper receive user or platform events.
- Wrapper resolves services from the container and delegates to core processors/actions.
- Core orchestrates PrestaShop adapters/repositories and api clients to read/write shop data and call PayPal.

References:
- Wrapper example: ps8/ps_checkout.php
- Core processor example: core/src/Order/Processor/CreateOrderProcessor.php
- HTTP client example: api/src/Http/OrderHttpClient.php
- Controller base: infrastructure/src/Controller/AbstractFrontController.php


2. Component interactions

- Module entry point and hooks (wrappers)
  - Each wrapper (ps17/ps_checkout.php, ps8/ps_checkout.php, ps9/ps_checkout.php) extends PaymentModule and registers hooks (e.g., paymentOptions, displayOrderConfirmation, order/shipping hooks).
  - Uses a service container (from Prestashop\\ModuleLibServiceContainer) to retrieve services such as LoggerInterface, actions, repositories, and presenters.

- Front-office controllers
  - Express Checkout: psX/controllers/front/ExpressCheckout.php parses JSON payload, updates the local PayPal order record, and executes core actions to prefill checkout and redirect to the order page (shipping selection).
  - Webhook endpoint: psX/controllers/front/webhook.php validates a shared secret header, parses JSON payload, and hands it over to core webhook handler.
  - Other FO controllers (cancel/return/check/applepay/dispatch) extend infrastructure/src/Controller/AbstractFrontController.php for shared behavior (service access, JSON responses).

- Core domain services
  - Order processing: core/src/Order/Processor/CreateOrderProcessor.php orchestrates checkout validation, PayPal order retrieval, capture, token management, and PrestaShop order creation.
  - Order state actions: core/src/OrderState/Action/* to set PS order states (Pending, Completed, Refunded, etc.).
  - Validators: core/src/Order/Validator/* enforce cart/order invariants and authorization.
  - PayPal provider: core/src/PayPal/Order/Provider/* to fetch PayPal order details.
  - Webhooks: core/src/Webhook/* includes token/secret services, payload parsing, event dispatching/handling.

- API clients
  - api/src/Http/* provides clients for Orders, Payments, Webhooks, and Shipment Tracking using PsrHttpClientAdapter and per-client configuration builders (api/src/Http/Configuration/*).
  - Errors normalized via api/src/Http/Exception/PayPalException.php and PayPalError.php.

- Infrastructure adapters and repositories
  - PrestaShop integration bridges: infrastructure/src/Adapter/* (ContextInterface, Configuration, Link, ShopContext).
  - Module persistence (e.g., mirrored PayPal order entity): infrastructure/src/Repository/*.

- Presentation layer
  - Presenters in presentation/Presenter/* prepare view-ready data for admin settings, FO payment options, funding sources, and order summaries.


3. Data flow diagrams

3.1 Standard checkout with PayPal capture

```
Customer -> FO Checkout page -> Module hook paymentOptions (psX/ps_checkout.php)
  -> JS (PayPal SDK) creates/approves PayPal order
  -> After approval: module route receives cart + PayPal orderId
  -> core CreateOrderProcessor.run(ValidateOrderRequest)
      -> checkoutValidator.validate(cartId, orderId)
      -> payPalOrderProvider.getById(orderId)         (api -> PayPal)
      -> orderAuthorizationValidator.validate(cart, payPalOrder)
      -> capturePayPalOrderAction.execute(payPalOrder) (api -> PayPal capture)
      -> savePaymentTokenAction.execute(response)
      -> createOrderAction.execute(capturedOrder)      (creates PS Order, sets state)
  -> Customer sees order confirmation
```

Key ref: core/src/Order/Processor/CreateOrderProcessor.php

3.2 Express Checkout (fast path)

```
Express button -> POST JSON -> psX/controllers/front/ExpressCheckout.php
  -> Parse payload (InputStreamUtility), update PayPalOrder (funding flags)
  -> Execute core ExpressCheckoutAction (prefill checkout)
  -> Redirect to order page for shipping selection
```

Key refs: ps8/controllers/front/ExpressCheckout.php, core/src/Customer/Action/ExpressCheckoutAction.php

3.3 Webhook handling

```
PayPal -> POST /module/ps_checkout/webhook
  -> Validate header webhook-secret
  -> Read JSON payload
  -> core WebhookHandler.handle(payload)
      -> Dispatch handlers by eventType (captures, refunds, config updates)
      -> Update local records / PS order states
  -> Reply 200 (or 401/400 on errors)
```

Key refs: ps8/controllers/front/webhook.php, core/src/Webhook/Handler/*

3.4 Shipment tracking propagation

```
Merchant updates tracking -> PS hook (e.g., order carrier update)
  -> Module actions: AddTrackingAction / ProcessExternalShipmentAction
  -> api OrderShipmentTrackingHttpClient -> PayPal tracking API
  -> PayPal reflects shipment state for buyer protection
```

Key refs: api/src/Http/OrderShipmentTrackingHttpClient.php, core/src/PayPal/ShippingTracking/Action/*, wrapper hooks in psX/ps_checkout.php


4. Design decisions and rationale

- Layered architecture to separate framework concerns, domain logic, and HTTP integrations, easing testing and PayPal evolution.
- Version wrappers per PrestaShop major to isolate version-specific hooks/routes while sharing common core, api, infrastructure, and presentation.
- Dependency Injection via service container for loose coupling and testability.
- HTTP client abstraction with configuration builders to centralize endpoints/auth and normalize errors.
- Repository/adapter patterns to encapsulate persistence and PrestaShop context/multishop logic.
- Event-driven reconciliation through webhooks for robustness against front-channel failures.
- Presenter pattern to prepare data for BO/FO templates and localizations.


5. System constraints and limitations

- Supported versions: PrestaShop 1.7, 8, and 9 (separate wrappers). Use the correct build for the target shop.
- PHP/extension requirements: Inherit the target PrestaShop requirements; module bundles dependencies in vendor/.
- Network & webhooks: Outbound calls to PayPal endpoints required; webhook route must be public and configured with the correct secret.
- Multishop: Configuration and state updates are shop-context aware via adapters (ShopContext).
- Currencies & funding sources: Availability depends on merchant/currency/country; presenters compute allowed options.
- Idempotency & error handling: Order processor guards against duplicates and maps PayPal errors to deterministic outcomes.
- Apple Pay: Domain association file must be properly served over HTTPS where applicable (see version-specific notes).
- Admin dependencies: Integration with PsAccountsInstaller / MBO installer; ensure versions are compatible in the target shop.


6. Version-specific details

Shared layers (core, api, infrastructure, presentation) are reused across versions. Wrappers under `ps17`, `ps8`, and `ps9` adapt hooks, routes, and UI specifics.

The detailed, per-version notes have been moved to the corresponding version folders:
- PrestaShop 1.7: ps17/ARCHITECTURE.md
- PrestaShop 8: ps8/ARCHITECTURE.md
- PrestaShop 9: ps9/ARCHITECTURE.md


7. Notable file references

- Wrappers and hooks:
  - ps17/ps_checkout.php
  - ps8/ps_checkout.php
  - ps9/ps_checkout.php
- FO controllers:
  - psX/controllers/front/ExpressCheckout.php
  - psX/controllers/front/webhook.php
- Controller base: infrastructure/src/Controller/AbstractFrontController.php
- Core orchestration: core/src/Order/Processor/CreateOrderProcessor.php
- Order state actions: core/src/OrderState/Action/*
- PayPal clients: api/src/Http/* and api/src/Http/Configuration/*
- Webhook handling: core/src/Webhook/*
- Presenters: presentation/Presenter/*

---

Maintenance note: This document references representative files and flows. If you introduce new major features (e.g., payment methods, asynchronous flows), update the relevant sections and add sequence/flow diagrams accordingly.

---
name: merchant-sdk-integration
description: >-
  Use when helping developers integrate the PrestaShop Checkout merchant SDK
  into a parent application (merchant backoffice). Covers loading the UMD bundle,
  creating and rendering the Zoid component, handling onSubmit callbacks,
  updating props dynamically, and full type/enum reference.
metadata:
  version: "2.0"
compatibility: Requires @krakenjs/zoid ^10.5.0.
---

# PrestaShop Checkout Merchant SDK Integration

## Quick Start

Load the UMD bundle via a `<script>` tag, then create and render the component:

```html
<!-- 1. Load the SDK bundle -->
<script src="https://your-cdn.example.com/merchant-sdk.umd.js"></script>

<!-- 2. Provide a container element -->
<div id="prestashop-checkout"></div>

<script>
  // 3. Create the component instance with props
  const checkout = window.PrestaShopCheckoutSDK.PrestaShopCheckout({
    url: "https://checkout-app.example.com",
    order: { /* Raw PayPal Order JSON */ },
    isTestMode: false,
    onSubmit: async (type, transactionId, data) => {
      // Handle the action (API call, etc.)
      return { message: "Action completed." };
    },
  });

  // 4. Render into the container
  checkout.render("#prestashop-checkout");
</script>
```

The bundle exposes `window.PrestaShopCheckoutSDK` with a `PrestaShopCheckout` factory function.

## onSubmit Callback

Called when the user triggers a transaction action (capture, void, reauthorize, refund) inside the embedded component.

```ts
onSubmit?: (
  type: TransactionActionType,
  transactionId: string,
  data?: TransactionActionData,
) => void | SubmitResult | Promise<void | SubmitResult>;
```

**Parameters:**

| Parameter       | Type                     | Description                                      |
|-----------------|--------------------------|--------------------------------------------------|
| `type`          | `TransactionActionType`  | The action: `"capture"`, `"void"`, `"reauthorize"`, or `"refund"` |
| `transactionId` | `string`                | The PayPal transaction ID the action applies to  |
| `data`          | `TransactionActionData?` | Optional action data (e.g., `{ amount: 50.00 }`) |

**Return value:**

- Return `void` or `undefined` for no feedback.
- Return `{ message: "..." }` (`SubmitResult`) to display a success message.
- Throw an `Error` to signal failure — the component will display the error.
- May be `async` (return a `Promise`).

## Component Props

```ts
interface PrestaShopCheckoutProps {
  url?: string;                                          // URL of the hosted child app (iframe src)
  order: PayPalOrder;                                    // Raw PayPal Order JSON from Orders v2 API
  isTestMode?: boolean;                                  // Display test mode indicator
  onSubmit?: (                                           // Action callback (see above)
    type: TransactionActionType,
    transactionId: string,
    data?: TransactionActionData,
  ) => void | SubmitResult | Promise<void | SubmitResult>;
}
```

- `url` — Defaults to `window.location.origin` if omitted.
- `order` — Required. The raw PayPal Order object from the Orders v2 API response.
- `isTestMode` — Optional. Displays a test/production mode indicator.
- `onSubmit` — Optional. Called when the user triggers a transaction action.

## Type Reference

### PayPalOrder (subset)

The SDK accepts the raw JSON response from PayPal's Orders v2 API. Key fields used:

```ts
interface PayPalOrder {
  id: string;
  status: OrderStatus;
  intent: OrderIntent;          // "CAPTURE" or "AUTHORIZE"
  purchase_units: PurchaseUnit[];
  payment_source?: PaymentSource;
  create_time?: string;
}

interface PurchaseUnit {
  reference_id?: string;
  amount: { currency_code: string; value: string };
  payments?: {
    authorizations?: PayPalAuthorization[];
    captures?: PayPalCapture[];
    refunds?: PayPalRefund[];
  };
}
```

Each authorization, capture, and refund object includes `id`, `status`, `amount`, `create_time`, and type-specific breakdown fields.

### TransactionActionData

```ts
interface TransactionActionData {
  amount?: number;
}
```

### SubmitResult

```ts
interface SubmitResult {
  message?: string;
}
```

## Enum Reference

All enums use PayPal-native SCREAMING_SNAKE_CASE values.

### TransactionActionType

| Key           | Value            |
|---------------|------------------|
| `Capture`     | `"capture"`      |
| `Void`        | `"void"`         |
| `Reauthorize` | `"reauthorize"`  |
| `Refund`      | `"refund"`       |

### OrderStatus

| Value                    | Description            |
|--------------------------|------------------------|
| `CREATED`                | Order created          |
| `SAVED`                  | Order saved            |
| `APPROVED`               | Payer approved         |
| `VOIDED`                 | Order voided           |
| `COMPLETED`              | Order completed        |
| `PAYER_ACTION_REQUIRED`  | Payer action needed    |

### OrderIntent

| Value       | Description                |
|-------------|----------------------------|
| `CAPTURE`   | Direct capture             |
| `AUTHORIZE` | Authorize then capture     |

### AuthorizationStatus

| Value               | Description         |
|---------------------|---------------------|
| `CREATED`           | Authorization created |
| `CAPTURED`          | Fully captured      |
| `DENIED`            | Authorization denied |
| `PARTIALLY_CAPTURED`| Partially captured  |
| `VOIDED`            | Authorization voided |
| `PENDING`           | Authorization pending |

### CaptureStatus

| Value               | Description           |
|---------------------|-----------------------|
| `COMPLETED`         | Capture completed     |
| `DECLINED`          | Capture declined      |
| `PARTIALLY_REFUNDED`| Partially refunded    |
| `PENDING`           | Capture pending       |
| `REFUNDED`          | Fully refunded        |
| `FAILED`            | Capture failed        |

### RefundStatus

| Value       | Description     |
|-------------|-----------------|
| `CANCELLED` | Refund cancelled |
| `FAILED`    | Refund failed   |
| `PENDING`   | Refund pending  |
| `COMPLETED` | Refund completed |

### SellerProtectionStatus

| Value                | Description         |
|----------------------|---------------------|
| `ELIGIBLE`           | Fully eligible      |
| `PARTIALLY_ELIGIBLE` | Partially eligible  |
| `NOT_ELIGIBLE`       | Not eligible        |

### LiabilityShift

| Value     | Description                |
|-----------|----------------------------|
| `POSSIBLE`| Liability shifted to bank  |
| `NO`      | Merchant bears liability   |
| `UNKNOWN` | Unknown liability shift    |

### PaymentMode

Unchanged from v1. Used internally by the SDK to display payment method logos. The SDK derives PaymentMode from `payment_source` automatically — you do not need to set it.

## Common Integration Scenarios

### Authorization + Capture Flow

An order in auth-capture mode where the merchant captures the full authorized amount:

```js
const checkout = window.PrestaShopCheckoutSDK.PrestaShopCheckout({
  url: "https://checkout-app.example.com",
  order: {
    id: "5O190127TN364715T",
    status: "APPROVED",
    intent: "AUTHORIZE",
    purchase_units: [{
      reference_id: "default",
      amount: { currency_code: "EUR", value: "125.50" },
      payments: {
        authorizations: [{
          id: "0AE12345BC678901D",
          status: "CREATED",
          amount: { currency_code: "EUR", value: "125.50" },
          create_time: "2025-12-01T10:00:00Z",
          expiration_time: "2025-12-30T10:00:00Z",
          seller_protection: { status: "ELIGIBLE" },
        }],
        captures: [],
        refunds: [],
      },
    }],
    payment_source: { paypal: {} },
  },
  isTestMode: false,
  onSubmit: async (type, transactionId, data) => {
    const response = await fetch("/api/checkout/action", {
      method: "POST",
      body: JSON.stringify({ type, transactionId, ...data }),
    });
    if (!response.ok) throw new Error("Action failed");
    return { message: `${type} completed successfully.` };
  },
});

checkout.render("#prestashop-checkout");
```

### Completed Payment with Partial Refund Available

```js
const checkout = window.PrestaShopCheckoutSDK.PrestaShopCheckout({
  url: "https://checkout-app.example.com",
  order: {
    id: "ORDER-002",
    status: "COMPLETED",
    intent: "CAPTURE",
    purchase_units: [{
      reference_id: "default",
      amount: { currency_code: "EUR", value: "200.00" },
      payments: {
        captures: [{
          id: "CAP-001",
          status: "COMPLETED",
          amount: { currency_code: "EUR", value: "200.00" },
          create_time: "2025-06-15T10:00:00Z",
          seller_protection: { status: "ELIGIBLE" },
          seller_receivable_breakdown: {
            gross_amount: { currency_code: "EUR", value: "200.00" },
            paypal_fee: { currency_code: "EUR", value: "5.80" },
            net_amount: { currency_code: "EUR", value: "194.20" },
          },
        }],
      },
    }],
    payment_source: {
      card: {
        brand: "VISA",
        last_digits: "4242",
        authentication_result: {
          liability_shift: "POSSIBLE",
          three_d_secure: {
            authentication_status: "Y",
            enrollment_status: "Y",
          },
        },
      },
    },
  },
  onSubmit: async (type, transactionId, data) => {
    const response = await fetch("/api/checkout/action", {
      method: "POST",
      body: JSON.stringify({ type, transactionId, ...data }),
    });
    if (!response.ok) throw new Error("Refund failed");
    return { message: `Refunded ${data?.amount} EUR.` };
  },
});

checkout.render("#prestashop-checkout");
```

### Refreshing Data After an Action

After a successful `onSubmit`, refresh the component by calling `updateProps` with fresh data:

```js
let checkoutInstance;

checkoutInstance = window.PrestaShopCheckoutSDK.PrestaShopCheckout({
  url: "https://checkout-app.example.com",
  order: currentOrder,
  onSubmit: async (type, transactionId, data) => {
    await performAction(type, transactionId, data);

    // Fetch updated data from your backend
    const updated = await fetch(`/api/orders/${currentOrder.id}`);
    const { order } = await updated.json();

    // Push new props into the iframe
    await checkoutInstance.updateProps({ order });

    return { message: "Action completed and data refreshed." };
  },
});

checkoutInstance.render("#prestashop-checkout");
```

### Test Mode

Set `isTestMode: true` to display a test mode indicator:

```js
const checkout = window.PrestaShopCheckoutSDK.PrestaShopCheckout({
  order: { /* ... */ },
  isTestMode: true,
});
```

## Architecture Notes

- **Zoid iframe**: The SDK uses `@krakenjs/zoid` to render the checkout component inside an iframe. The parent page loads the UMD bundle which registers the Zoid component. The child app runs inside the iframe.
- **`window.xprops`**: Inside the iframe, the child app accesses parent-provided props via `window.xprops`. It listens for dynamic updates via `window.xprops.onProps(callback)`.
- **`url` prop**: The `url` prop sets the iframe `src`. It defaults to `window.location.origin`. In production, point it to your hosted child app URL.
- **Data derivation**: The SDK derives all display data (financials, payment mode, transaction list, 3DS status) from the raw PayPal Order JSON using internal composables. The parent only needs to pass the raw API response.
- **Build commands**:
  - `yarn run dev` — Dev server with playground at `http://localhost:5173/playground.html`
  - `yarn run build` — Build the child app to `app/dist/`
  - `yarn run build:sdk` — Build the UMD bundle to `dist/sdk/merchant-sdk.umd.js`
  - `yarn run serve:sdk` — Preview the UMD bundle on port `5174`
- **Playground**: Access `http://localhost:5173/playground.html` during development to simulate the parent application with editable JSON props.

# Authorization Refund Order Status — Analysis & Proposed Fix

## QA Feedback

> A full refund is issued for partial capture orders. The order is considered refunded even though the total amount has not been fully captured.

## Scenario

| Step | Action                 | Amount | Order Status                           |
|------|------------------------|--------|----------------------------------------|
| 1    | Order created          | €22.94 | Waiting for payment                    |
| 2    | Authorization          | €22.94 | Authorized. To be captured by merchant |
| 3    | Partial capture        | €10.00 | Partial payment                        |
| 4    | Full refund of capture | €10.00 | **Remboursé (Refunded)**               |

The order ends up as "Refunded" even though the full order amount (€22.94) was never captured — only €10.00 was captured and then refunded.

## Root Cause

In `core/src/OrderState/Action/SetRefundedOrderStateAction.php`, the `handleAuthorizationRefund` method (line 133) compares **total refunded** against **total captured**:

```php
if ($totalRefunded < $totalCaptured) {
    $newOrderState = PARTIALLY_REFUNDED;
} else if ($totalRefunded === $totalCaptured) {
    $newOrderState = REFUNDED;  // <-- This is the problem
}
```

In our scenario: `$totalRefunded (10.00) === $totalCaptured (10.00)` → **REFUNDED**.

The comparison ignores the order total. From the merchant's perspective, "Refunded" implies the full order value (€22.94) was returned to the customer, which is misleading.

Note: there is already a `//TODO: Check this logic for authorization refunds` comment on line 132, flagging this logic as needing review.

## Decision Point

**Should "Refunded" only apply when the full order amount was captured and then fully refunded?**

- **Option A**: If the capture was partial, the order should be "Partially refunded" even if 100% of the captured amount is refunded. Rationale: from the order's perspective, only a fraction of the total was ever collected and returned.
- **Option B**: Keep current behavior — "Refunded" means all captured money was returned, regardless of the order total. Rationale: the customer has no outstanding charges, and the authorization will expire.

## Proposed Fix (Option A)

```php
private function handleAuthorizationRefund(
    PayPalRefundOrder $refundOrder,
    PayPalOrderResponse $payPalOrderResponse
) {
    $orderTotal = (float) $refundOrder->getTotalAmount();

    $totalCaptured = array_reduce($payPalOrderResponse->getCaptures(), function ($totalCaptured, $capture) {
        return $totalCaptured + (float) $capture['amount']['value'];
    });

    $totalRefunded = array_reduce($payPalOrderResponse->getRefunds(), function ($totalRefunded, $refund) {
        return $totalRefunded + (float) $refund['amount']['value'];
    });

    $newOrderState = null;

    if ($totalRefunded >= $totalCaptured && round($totalCaptured, 2) >= round($orderTotal, 2)) {
        // Full capture was fully refunded → truly "Refunded"
        $newOrderState = $this->orderStateMapper->getIdByKey(
            OrderStateConfiguration::PS_CHECKOUT_STATE_REFUNDED
        );
    } elseif ($totalRefunded > 0) {
        // Any other refund scenario (partial refund, or full refund of partial capture)
        $newOrderState = $this->orderStateMapper->getIdByKey(
            OrderStateConfiguration::PS_CHECKOUT_STATE_PARTIALLY_REFUNDED
        );
    }

    if ($newOrderState && $refundOrder->getCurrentStateId() !== $newOrderState) {
        $this->changeOrderStateAction->execute($refundOrder->getOrderId(), $newOrderState);
    }
}
```

### Behavior with the fix

| Scenario                          | Captured | Refunded | Order Total | Status                           |
|-----------------------------------|----------|----------|-------------|----------------------------------|
| Partial refund of partial capture | €10      | €5       | €22.94      | Partially refunded               |
| Full refund of partial capture    | €10      | €10      | €22.94      | **Partially refunded** (changed) |
| Partial refund of full capture    | €22.94   | €10      | €22.94      | Partially refunded               |
| Full refund of full capture       | €22.94   | €22.94   | €22.94      | Refunded                         |

## Open Questions

1. Should we also consider the authorization status (e.g., voided vs still active) when determining the order state?
2. Are there downstream effects of keeping a partially-captured, fully-refunded order as "Partially refunded" instead of "Refunded"? (e.g., accounting reports, module behaviors)

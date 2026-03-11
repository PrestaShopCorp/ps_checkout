---
name: paypal-error-audit
description: >-
  Audit PayPal error handling coverage against the official PayPal API documentation.
  Fetches latest PayPal OpenAPI specs, compares against module error mappings,
  checks customer and merchant message coverage, validates translations,
  auto-fixes gaps, and creates a draft PR. Use when PayPal updates their API
  or periodically to ensure full error coverage.
---

# PayPal Error Audit

Perform a full gap analysis of PayPal error handling in the ps_checkout module against the official PayPal API documentation. Then auto-fix gaps and create a draft PR.

## Steps

### 1. Error Code Sync

Fetch the PayPal OpenAPI specs from GitHub:
- `https://raw.githubusercontent.com/paypal/paypal-rest-api-specifications/main/openapi/checkout_orders_v2.json`
- `https://raw.githubusercontent.com/paypal/paypal-rest-api-specifications/main/openapi/payments_payment_v2.json`

Extract all `issue` values from error response schemas. Compare against:
- `PayPalException` constants in `api/src/Http/Exception/PayPalException.php`
- `PayPalError` switch cases in `api/src/Http/Exception/PayPalError.php`

Report missing codes with their API operation context.

### 2. Customer Message Coverage

Read `core/src/Order/Exception/Handler/OrderCreationExceptionHandler.php`. Extract all `PayPalException::*` codes handled in the switch block. Compare against the full set of `PayPalException` constants.

Report codes that lack a customer-facing message, prioritized by likelihood:
1. Card errors (highest priority)
2. Order errors
3. Merchant configuration errors (lowest priority)

### 3. Admin Refund Message Coverage

Read the admin controllers' `ajaxProcessRefundOrder()` method in:
- `ps8/controllers/admin/AdminAjaxPrestashopCheckoutController.php`
- `ps9/controllers/admin/AdminAjaxPrestashopCheckoutController.php`
- `ps17/controllers/admin/AdminAjaxPrestashopCheckoutController.php`

Check which `PayPalException` refund-related codes (`REFUND_*`, `CAPTURE_*`, `PENDING_CAPTURE`, `CANNOT_PROCESS_REFUNDS`, `TRANSACTION_DISPUTED`, `CURRENCY_MISMATCH`) are handled in the `catch (PayPalException ...)` block vs falling through to the generic catch.

### 4. Translation Validation

Grep for all error message strings in:
- `core/src/Order/Exception/Handler/OrderCreationExceptionHandler.php`
- All 3 admin controllers

Verify each is wrapped in `$this->translator->trans()` or `$translator->trans()`. Flag any hardcoded strings.

## Output Format

```
## PayPal Error Audit Report

### Error Code Sync
- X codes in PayPal docs, Y handled in module, Z missing
- Missing codes: [list with API operation context]

### Customer Message Coverage
- X PayPalException codes total, Y with customer messages, Z without
- Priority gaps: [card errors first, then order errors, then merchant errors]

### Admin Refund Message Coverage
- X refund-related codes, Y handled, Z falling to generic catch
- Missing: [list]

### Translation Validation
- X message strings checked, Y properly wrapped, Z hardcoded
- Issues: [list with file:line]
```

## Auto-Fix

After generating the report, auto-fix all identified gaps following these patterns:

1. **New constants**: Add to `PayPalException.php` after the last constant, incrementing the integer value
2. **New switch cases**: Add to `PayPalError.php` before `default:`, with a concise message from PayPal docs
3. **Customer messages**: Add to `OrderCreationExceptionHandler.php` in the `PayPalException::class` switch block. Card errors → `$notifyCustomerService = false` + httpCode 400. Merchant errors → `$notifyCustomerService = true` + httpCode 500.
4. **Refund messages**: Add to admin controllers' `catch (PayPalException ...)` block. Apply identically to all 3 versions (ps9 uses `\Exception`, ps8/ps17 use `Exception`).

Then run:
```bash
make php-cs-fixer
make phpstan
make unit-test
```

Finally, create a draft PR with the fixes.

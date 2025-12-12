<?php

namespace PsCheckout\src\PayPal\Order\Configuration;

class PayPalAuthorizationStatus
{
    const CREATED = 'CREATED';

    const CAPTURED = 'CAPTURED';

    const DENIED = 'DENIED';

    const PARTIALLY_CAPTURED = 'PARTIALLY_CAPTURED';

    const VOIDED = 'VOIDED';

    const PENDING = 'PENDING';
}

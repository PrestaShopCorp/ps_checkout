<?php

namespace PsCheckout\Api\Dto\PayPal\Payment;

/**
 * The status for the authorized payment.
 */
class AuthorizationLinkRelation
{
    public const SELF = 'self';

    public const CAPTURE = 'capture';

    public const VOID = 'void';

    public const REAUTHORIZE = 'reauthorize';
}

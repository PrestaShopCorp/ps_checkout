<?php

namespace PsCheckout\Api\Dto\PayPal;

/**
 * The condition that is covered for the transaction.
 */
class DisputeCategory
{
    /**
     * The payer paid for an item that they did not receive.
     */
    public const ITEM_NOT_RECEIVED = 'ITEM_NOT_RECEIVED';

    /**
     * The payer did not authorize the payment.
     */
    public const UNAUTHORIZED_TRANSACTION = 'UNAUTHORIZED_TRANSACTION';

    public const CATEGORIES = [self::ITEM_NOT_RECEIVED, self::UNAUTHORIZED_TRANSACTION];
}
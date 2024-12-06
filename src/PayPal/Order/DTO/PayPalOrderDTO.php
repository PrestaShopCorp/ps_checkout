<?php

declare(strict_types=1);

namespace PrestaShop\Module\PrestashopCheckout\PayPal\Order\DTO;

class PayPalOrderDTO
{

    /**
     * @var int
     */
    private $id;
    /**
     * @var int
     */
    private $idCart;
    /**
     * @var string
     */
    private $intent;
    /**
     * @var string
     */
    private $fundingSource;
    /**
     * @var string
     */
    private $status;
    /**
     * @var array
     */
    private $paymentSource;
    /**
     * @var string
     */
    private $environment;
    /**
     * @var bool
     */
    private $isCardFields;
    /**
     * @var bool
     */
    private $isExpressCheckout;
    /**
     * @var array
     */
    private $customerIntent;
    /**
     * @var int|null
     */
    private $paymentTokenId;

    public static function createFromMasslandResponse(array $data): PayPalOrderDTO
    {
        return new PayPalOrderDTO();
    }
}

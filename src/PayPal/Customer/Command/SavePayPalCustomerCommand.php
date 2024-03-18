<?php

namespace PrestaShop\Module\PrestashopCheckout\PayPal\Customer\Command;

use PrestaShop\Module\PrestashopCheckout\Customer\ValueObject\CustomerId;
use PrestaShop\Module\PrestashopCheckout\PayPal\Customer\ValueObject\PayPalCustomerId;

class SavePayPalCustomerCommand
{
    /**
     * @var CustomerId
     */
    private $customerId;
    /**
     * @var PayPalCustomerId
     */
    private $payPalCustomerId;

    public function __construct(CustomerId $customerId, PayPalCustomerId $payPalCustomerId)
    {
        $this->customerId = $customerId;
        $this->payPalCustomerId = $payPalCustomerId;
    }

    /**
     * @return CustomerId
     */
    public function getCustomerId()
    {
        return $this->customerId;
    }

    /**
     * @return PayPalCustomerId
     */
    public function getPayPalCustomerId()
    {
        return $this->payPalCustomerId;
    }
}

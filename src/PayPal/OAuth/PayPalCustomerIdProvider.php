<?php

namespace PrestaShop\Module\PrestashopCheckout\PayPal\OAuth;

use PrestaShop\Module\PrestashopCheckout\Customer\ValueObject\CustomerId;
use PrestaShop\Module\PrestashopCheckout\PayPal\PayPalConfiguration;
use PrestaShop\Module\PrestashopCheckout\Repository\PayPalCustomerRepository;

class PayPalCustomerIdProvider
{
    private OAuthService $OAuthService;
    private PayPalCustomerRepository $customerRepository;
    private PayPalConfiguration $payPalConfiguration;

    public function __construct(OAuthService $OAuthService, PayPalCustomerRepository $customerRepository, PayPalConfiguration $payPalConfiguration)
    {
        $this->OAuthService = $OAuthService;
        $this->customerRepository = $customerRepository;
        $this->payPalConfiguration = $payPalConfiguration;
    }

    public function getCustomerId(CustomerId $customerId = null)
    {
        $customerIdPayPal = $customerId ? $this->customerRepository->findPayPalCustomerIdByCustomerId($customerId) : null;
        $merchantId = $this->payPalConfiguration->getMerchantId();

        return $this->OAuthService->getUserIdToken($merchantId, $customerIdPayPal);
    }
}

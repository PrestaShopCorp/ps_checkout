<?php

/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

namespace PrestaShop\Module\PrestashopCheckout\PayPal\OAuth\Query;

use PrestaShop\Module\PrestashopCheckout\PayPal\OAuth\OAuthService;
use PrestaShop\Module\PrestashopCheckout\PayPal\PayPalConfiguration;
use PrestaShop\Module\PrestashopCheckout\Repository\PayPalCustomerRepository;

class GetPayPalGetUserIdTokenQueryHandler
{
    public function __construct(
        private OAuthService $OAuthService,
        private PayPalCustomerRepository $customerRepository,
        private PayPalConfiguration $payPalConfiguration,
    ) {
    }

    /**
     * @param GetPayPalGetUserIdTokenQuery $query
     *
     * @return GetPayPalGetUserIdTokenQueryResult
     *
     * @throws \Exception
     */
    public function __invoke(GetPayPalGetUserIdTokenQuery $query)
    {
        $customerIdPayPal = $query->getCustomerId() ? $this->customerRepository->findPayPalCustomerIdByCustomerId($query->getCustomerId()) : null;
        $merchantId = $this->payPalConfiguration->getMerchantId();

        return new GetPayPalGetUserIdTokenQueryResult($this->OAuthService->getUserIdToken($merchantId, $customerIdPayPal));
    }
}

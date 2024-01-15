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

namespace PrestaShop\Module\PrestashopCheckout\PaymentMethodToken\Query;

use Exception;
use PrestaShop\Module\PrestashopCheckout\PaymentMethodToken\PaymentMethodTokenRepository;
use PrestaShop\Module\PrestashopCheckout\PayPal\Customer\PayPalCustomerRepository;

class GetCustomerPaymentMethodTokensQueryHandler
{
    /**
     * @var PayPalCustomerRepository
     */
    private $customerRepository;

    /**
     * @var PaymentMethodTokenRepository
     */
    private $paymentMethodTokenRepository;

    /**
     * @param PayPalCustomerRepository $customerRepository
     * @param PaymentMethodTokenRepository $paymentMethodTokenRepository
     */
    public function __construct(PayPalCustomerRepository $customerRepository, PaymentMethodTokenRepository $paymentMethodTokenRepository)
    {
        $this->customerRepository = $customerRepository;
        $this->paymentMethodTokenRepository = $paymentMethodTokenRepository;
    }

    /**
     * @param GetCustomerPaymentMethodTokensQuery $query
     *
     * @return GetCustomerPaymentMethodTokensQueryResult
     *
     * @throws Exception
     */
    public function handle(GetCustomerPaymentMethodTokensQuery $query)
    {
        $customerIdPayPal = $query->getCustomerId() ? $this->customerRepository->findPayPalCustomerIdByCustomerId($query->getCustomerId()) : null;
        $paymentTokens = $this->paymentMethodTokenRepository->findByCustomerId($customerIdPayPal, $query->getPageSize(), $query->getPageNumber());

        if ($query->isTotalCountRequired()) {
            $totalItems = $this->paymentMethodTokenRepository->getTotalItems($customerIdPayPal);
            $totalPages = ceil($totalItems / $query->getPageSize());
        } else {
            $totalItems = null;
            $totalPages = null;
        }

        return new GetCustomerPaymentMethodTokensQueryResult(
            $paymentTokens,
            $query->getCustomerId(),
            $totalItems,
            $totalPages
        );
    }
}

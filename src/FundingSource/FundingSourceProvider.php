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

namespace PrestaShop\Module\PrestashopCheckout\FundingSource;

use PrestaShop\Module\PrestashopCheckout\PayPal\PayPalConfiguration;
use PrestaShop\Module\PrestashopCheckout\Repository\PaymentTokenRepository;

class FundingSourceProvider
{
    /**
     * @var FundingSourceCollection
     */
    private $collection;

    /**
     * @var FundingSourcePresenter
     */
    private $presenter;
    /**
     * @var PaymentTokenRepository
     */
    private $paymentTokenRepository;
    /**
     * @var PayPalConfiguration
     */
    private $payPalConfiguration;

    /**
     * @param FundingSourceCollectionBuilder $fundingSourceCollectionBuilder
     * @param FundingSourcePresenter $presenter
     * @param PaymentTokenRepository $paymentTokenRepository
     * @param PayPalConfiguration $payPalConfiguration
     */
    public function __construct(
        FundingSourceCollectionBuilder $fundingSourceCollectionBuilder,
        FundingSourcePresenter $presenter,
        PaymentTokenRepository $paymentTokenRepository,
        PayPalConfiguration $payPalConfiguration
    ) {
        $this->collection = new FundingSourceCollection($fundingSourceCollectionBuilder->create());
        $this->presenter = $presenter;
        $this->paymentTokenRepository = $paymentTokenRepository;
        $this->payPalConfiguration = $payPalConfiguration;
    }

    /**
     * Get all the funding sources
     *
     * @param bool $isAdmin
     *
     * @return FundingSource[]
     */
    public function getAll($isAdmin = false)
    {
        $fundingSources = [];
        $collection = $this->collection->sortByPosition();

        if (false === $isAdmin) {
            $collection = $collection->filterEligibility();
        }

        foreach ($collection->get() as $fundingSource) {
            $fundingSources[] = $this->presenter->present($fundingSource, $isAdmin);
        }

        return $fundingSources;
    }

    /**
     * @param int $customerId
     *
     * @return FundingSource[]
     *
     * @throws \PrestaShopDatabaseException
     */
    public function getSavedTokens($customerId)
    {
        if ((int) $customerId && $this->payPalConfiguration->isVaultingEnabled()) {
            return array_map(function ($paymentToken) {
                return $this->presenter->presentPaymentToken($paymentToken);
            }, $this->paymentTokenRepository->findByPrestaShopCustomerId((int) $customerId, true, $this->payPalConfiguration->getMerchantId()));
        }

        return [];
    }
}

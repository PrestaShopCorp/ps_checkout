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

namespace PrestaShop\Module\PrestashopCheckout\Validator;

use PrestaShop\Module\PrestashopCheckout\Context\PrestaShopContext;
use PrestaShop\Module\PrestashopCheckout\Repository\PaypalAccountRepository;
use PrestaShop\Module\PrestashopCheckout\Repository\PsAccountRepository;
use PrestaShop\Module\PrestashopCheckout\ShopUuidManager;

class MerchantValidator
{
    /**
     * @var PaypalAccountRepository
     */
    private $paypalAccountRepository;
    /**
     * @var PsAccountRepository
     */
    private $psAccountRepository;
    /**
     * @var PrestaShopContext
     */
    private $prestaShopContext;
    /**
     * @var ShopUuidManager
     */
    private $shopUuidManager;

    public function __construct(
        PaypalAccountRepository $paypalAccountRepository,
        PsAccountRepository $psAccountRepository,
        PrestaShopContext $prestaShopContext,
        ShopUuidManager $shopUuidManager
    ) {
        $this->paypalAccountRepository = $paypalAccountRepository;
        $this->psAccountRepository = $psAccountRepository;
        $this->prestaShopContext = $prestaShopContext;
        $this->shopUuidManager = $shopUuidManager;
    }

    public function merchantIsValid()
    {
        $shopUuid = $this->shopUuidManager->getForShop((int) $this->prestaShopContext->getShopId());

        return $this->paypalAccountRepository->onBoardingIsCompleted()
            && $this->paypalAccountRepository->paypalEmailIsValid()
            && $this->paypalAccountRepository->paypalPaymentMethodIsValid()
            && $this->psAccountRepository->onBoardingIsCompleted()
            && $shopUuid;
    }
}

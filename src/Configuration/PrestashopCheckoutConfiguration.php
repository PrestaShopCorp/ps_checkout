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

namespace PrestaShop\Module\PrestashopCheckout\Configuration;

use PrestaShop\Module\PrestashopCheckout\Api\Firebase\Token;
use PrestaShop\Module\PrestashopCheckout\Repository\PaypalAccountRepository;
use PrestaShop\Module\PrestashopCheckout\Repository\PsAccountRepository;

class PrestashopCheckoutConfiguration
{
    /**
     * @var \PrestaShop\Module\PrestashopCheckout\Configuration\PrestaShopConfiguration
     */
    private $prestashopConfiguration;

    /**
     * @var \PrestaShop\Module\PrestashopCheckout\Repository\PaypalAccountRepository
     */
    private $paypalAccount;

    /**
     * @var \PrestaShop\Module\PrestashopCheckout\Repository\PsAccountRepository
     */
    private $psAccount;

    /**
     * @param PrestaShopConfiguration $prestashopConfiguration
     * @param PaypalAccountRepository $paypalAccount
     * @param PsAccountRepository $psAccount
     */
    public function __construct(
        PrestaShopConfiguration $prestashopConfiguration,
        PaypalAccountRepository $paypalAccount,
        PsAccountRepository $psAccount
    ) {
        $this->prestashopConfiguration = $prestashopConfiguration;
        $this->paypalAccount = $paypalAccount;
        $this->psAccount = $psAccount;
    }

    /**
     * Get Firebase configuration for PrestaShop Checkout
     *
     * @return array
     */
    public function getFirebase()
    {
        $token = new Token();

        return [
            'email' => $this->psAccount->getEmail(),
            'token' => $token->getToken(),
            'accountId' => $this->psAccount->getLocalId(),
            'refreshToken' => $this->psAccount->getRefreshToken(),
        ];
    }

    /**
     * Get shop data for PrestaShop Checkout
     *
     * @return array
     */
    public function getShopData()
    {
        return [
            'psxForm' => $this->psAccount->getPsxForm(),
        ];
    }

    /**
     * Get PayPal configuration for PrestaShop Checkout
     *
     * @return array
     */
    public function getPaypal()
    {
        return [
            'merchantId' => $this->paypalAccount->getMerchantId(),
        ];
    }
}

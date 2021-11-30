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
     * @var PaypalAccountRepository
     */
    private $paypalAccount;

    /**
     * @var PsAccountRepository
     */
    private $psAccount;
    /**
     * @var Token
     */
    private $firebaseToken;

    /**
     * @param PaypalAccountRepository $paypalAccount
     * @param PsAccountRepository $psAccount
     * @param Token $firebaseToken
     */
    public function __construct(
        PaypalAccountRepository $paypalAccount,
        PsAccountRepository $psAccount,
        Token $firebaseToken
    ) {
        $this->paypalAccount = $paypalAccount;
        $this->psAccount = $psAccount;
        $this->firebaseToken = $firebaseToken;
    }

    /**
     * Get Firebase configuration for PrestaShop Checkout
     *
     * @return array
     */
    public function getFirebase()
    {
        return [
            'email' => $this->psAccount->getEmail(),
            'token' => $this->firebaseToken->getToken(),
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

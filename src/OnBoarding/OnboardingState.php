<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */

namespace PrestaShop\Module\PrestashopCheckout\OnBoarding;

use PrestaShop\Module\PrestashopCheckout\Api\Firebase\Token;
use PrestaShop\Module\PrestashopCheckout\Configuration\PrestashopCheckoutConfiguration;

class OnboardingState
{
    /**
     * @var \PrestaShop\Module\PrestashopCheckout\Configuration\PrestashopCheckoutConfiguration
     */
    private $psCheckoutConfiguration;

    /**
     * @param \PrestaShop\Module\PrestashopCheckout\Configuration\PrestashopCheckoutConfiguration $psCheckoutConfiguration
     */
    public function __construct(PrestashopCheckoutConfiguration $psCheckoutConfiguration)
    {
        $this->psCheckoutConfiguration = $psCheckoutConfiguration;
    }

    /**
     * Check if the merchant is already onboarded with Firebase
     *
     * @return bool
     */
    public function isFirebaseOnboarded()
    {
        $firebaseToken = new Token();

        return !empty($firebaseToken->getToken()) ?: false;
    }

    /**
     * Check if the shop data are already collected (Form business)
     *
     * @return bool
     */
    public function isShopDataCollected()
    {
        return !empty($this->psCheckoutConfiguration->getShopData()['psxForm']) ?: false;
    }
}

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

namespace PrestaShop\Module\PrestashopCheckout\OnBoarding;

use PrestaShop\Module\PrestashopCheckout\Api\Firebase\Token;
use PrestaShop\Module\PrestashopCheckout\Configuration\PrestashopCheckoutConfiguration;
use PrestaShop\Module\PrestashopCheckout\Repository\PaypalAccountRepository;

class OnboardingState
{
    /**
     * @var PrestashopCheckoutConfiguration
     */
    private $psCheckoutConfiguration;

    /**
     * @var PaypalAccountRepository
     */
    private $paypalAccountRepository;
    /**
     * @var Token
     */
    private $firebaseToken;

    /**
     * @param PrestashopCheckoutConfiguration $psCheckoutConfiguration
     * @param PaypalAccountRepository $paypalAccountRepository
     * @param Token $firebaseToken
     */
    public function __construct(
        PrestashopCheckoutConfiguration $psCheckoutConfiguration,
        PaypalAccountRepository $paypalAccountRepository,
        Token $firebaseToken
    )
    {
        $this->psCheckoutConfiguration = $psCheckoutConfiguration;
        $this->paypalAccountRepository = $paypalAccountRepository;
        $this->firebaseToken = $firebaseToken;
    }

    /**
     * Check if the merchant is already onboarded on Firebase
     *
     * @return bool
     */
    public function isFirebaseOnboarded()
    {
        return !empty($this->firebaseToken->getToken());
    }

    /**
     * Check if the shop data is already collected (Business form)
     *
     * @return bool
     */
    public function isShopDataCollected()
    {
        return !empty($this->psCheckoutConfiguration->getShopData()['psxForm']);
    }

    /**
     * Check if the merchant is already onboarded on PayPal
     *
     * @return bool
     */
    public function isPaypalOnboarded()
    {
        return $this->paypalAccountRepository->onBoardingIsCompleted();
    }
}

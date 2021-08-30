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

namespace PrestaShop\Module\PrestashopCheckout\Presenter\Store\Modules;

use PrestaShop\Module\PrestashopCheckout\Presenter\PresenterInterface;
use PrestaShop\Module\PrestashopCheckout\Repository\PaypalAccountRepository;

/**
 * Construct the paypal module
 */
class PaypalModule implements PresenterInterface
{
    /**
     * @var PaypalAccountRepository
     */
    private $paypalAccount;

    /**
     * @param PaypalAccountRepository $paypalAccountRepository
     */
    public function __construct(PaypalAccountRepository $paypalAccountRepository)
    {
        $this->paypalAccount = $paypalAccountRepository;
    }

    /**
     * Present the paypal module (vuex)
     *
     * @return array
     */
    public function present()
    {
        $paypalAccount = $this->paypalAccount->getOnboardedAccount();

        return [
            'paypal' => [
                'idMerchant' => $paypalAccount->getMerchantId(),
                'paypalOnboardingLink' => '',
                'onboardingCompleted' => !empty($paypalAccount->getMerchantId()),
                'accountIslinked' => !empty($paypalAccount->getEmail()) && !empty($paypalAccount->getMerchantId()),
                'emailMerchant' => $paypalAccount->getEmail(),
                'emailIsValid' => $paypalAccount->getEmailIsVerified(),
                'cardIsActive' => $paypalAccount->getCardPaymentStatus(),
                'paypalIsActive' => $paypalAccount->getPaypalPaymentStatus(),
                'countryMerchant' => $paypalAccount->getMerchantCountry(),
            ],
        ];
    }
}

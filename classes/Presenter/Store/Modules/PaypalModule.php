<?php
/**
 * 2007-2019 PrestaShop and Contributors
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2019 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaShop SA
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
     * Present the paypal module (vuex)
     *
     * @return array
     */
    public function present()
    {
        $idMerchant = (new PaypalAccountRepository())->getMerchantId();

        $paypalModule = [
            'paypal' => [
                'idMerchant' => $idMerchant,
                'paypalOnboardingLink' => '',
                'onboardingCompleted' => !empty($idMerchant),
                'emailMerchant' => \Configuration::get('PS_CHECKOUT_PAYPAL_EMAIL_MERCHANT'),
                'emailIsValid' => \Configuration::get('PS_CHECKOUT_PAYPAL_EMAIL_STATUS'),
                'cardIsActive' => \Configuration::get('PS_CHECKOUT_CARD_PAYMENT_STATUS'),
                'paypalIsActive' => \Configuration::get('PS_CHECKOUT_PAYPAL_PAYMENT_STATUS'),
            ],
        ];

        return $paypalModule;
    }
}

<?php
/**
* 2007-2019 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2019 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

namespace PrestaShop\Module\PrestashopCheckout\Presenter\Store\Modules;

use PrestaShop\Module\PrestashopCheckout\Api\Payment\Onboarding;
use PrestaShop\Module\PrestashopCheckout\Repository\PaypalAccountRepository;
use PrestaShop\Module\PrestashopCheckout\Presenter\Store\StorePresenterInterface;

/**
 * Construct the paypal module
 */
class PaypalModule implements StorePresenterInterface
{
    /**
     * Present the paypal module (vuex)
     *
     * @return array
     */
    public function present()
    {
        $idMerchant = (new PaypalAccountRepository())->getMerchantId();

        $paypalModule = array(
            'paypal' => array(
                'idMerchant' => $idMerchant,
                'paypalOnboardingLink' => $this->getPaypalOnboardingLink(),
                'onboardingCompleted' => !empty($idMerchant),
                'emailMerchant' => \Configuration::get('PS_CHECKOUT_PAYPAL_EMAIL_MERCHANT'),
                'emailIsValid' => \Configuration::get('PS_CHECKOUT_PAYPAL_EMAIL_STATUS'),
                'cardIsActive' => \Configuration::get('PS_CHECKOUT_CARD_PAYMENT_STATUS'),
                'paypalIsActive' => \Configuration::get('PS_CHECKOUT_PAYPAL_PAYMENT_STATUS'),
            ),
        );

        return $paypalModule;
    }

    /**
     * Generate the paypal onboarding link
     *
     * @return string|bool paypal onboarding link
     */
    private function getPaypalOnboardingLink()
    {
        $paypalAccount = new PaypalAccountRepository();

        if (true === $paypalAccount->onbardingIsCompleted()) {
            return false;
        }

        $context = \Context::getContext();

        $language = \Language::getLanguage($context->employee->id_lang);
        $locale = $language['locale'];

        $paypalOnboardingLink = (new Onboarding($context->link))->getOnboardingLink($locale);

        return $paypalOnboardingLink;
    }
}

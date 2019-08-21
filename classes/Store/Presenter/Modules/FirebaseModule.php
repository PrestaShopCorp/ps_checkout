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

namespace PrestaShop\Module\PrestashopCheckout\Store\Presenter\Modules;

use PrestaShop\Module\PrestashopCheckout\FirebaseClient;
use PrestaShop\Module\PrestashopCheckout\Store\Presenter\StorePresenterInterface;
use PrestaShop\Module\PrestashopCheckout\Environment\SsoEnv;

/**
 * Construct the firebase module
 */
class FirebaseModule implements StorePresenterInterface
{
    /**
     * @var \Context
     */
    private $context;

    public function __construct(\Context $context)
    {
        $this->context = $context;
    }

    /**
     * Present the paypal module (vuex)
     *
     * @return array
     */
    public function present()
    {
        $idToken = (new FirebaseClient())->getToken();

        $firebaseModule = array(
            'firebase' => array(
                'email' => \Configuration::get('PS_PSX_FIREBASE_EMAIL'),
                'idToken' => $idToken,
                'localId' => \Configuration::get('PS_PSX_FIREBASE_LOCAL_ID'),
                'refreshToken' => \Configuration::get('PS_PSX_FIREBASE_REFRESH_TOKEN'),
                'onboardingLinkSignIn' => $this->getOnboardingLink('login'),
                'onboardingLinkCreateAccount' => $this->getOnboardingLink('createaccount'),
                'onboardingLinkLogout' => $this->getOnboardingLink('logout'),
                'onboardingCompleted' => !empty($idToken),
            ),
        );

        return $firebaseModule;
    }

    /**
     * Undocumented function
     *
     * @param string $mode can be signin or createaccount
     *
     * @return string SSO url to onboard the merchant on psx
     */
    private function getOnboardingLink($mode)
    {
        $callbackUrl = $this->context->link->getAdminLink('AdminPsxOnboardingPrestashopCheckout');

        return (new SsoEnv())->getSsoUrl() . $this->getSsoIsoCode() .'/' . $mode . '?continue=' . $callbackUrl;
    }

    /**
     * Temporary method until SSO check automatically locale of the browser to set the language.
     * It allows to get the iso code to use in the sso url
     *
     * @return string iso code
     */
    private function getSsoIsoCode()
    {
        $availableSsoLanguages = ['de', 'en', 'es', 'fr', 'it', 'nl', 'pl', 'pt', 'ru'];

        $currentLanguageIsoCode = $this->context->language->iso_code;

        if (in_array($currentLanguageIsoCode, $availableSsoLanguages)) {
            return $currentLanguageIsoCode;
        }

        return 'en';
    }
}

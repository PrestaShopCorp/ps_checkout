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

namespace PrestaShop\Module\PrestashopCheckout\Presenter\Store\Modules;

use PrestaShop\Module\PrestashopCheckout\Adapter\LinkAdapter;
use PrestaShop\Module\PrestashopCheckout\Context\PrestaShopContext;
use PrestaShop\Module\PrestashopCheckout\Faq\Faq;
use PrestaShop\Module\PrestashopCheckout\OnBoarding\Step\LiveStep;
use PrestaShop\Module\PrestashopCheckout\OnBoarding\Step\ValueBanner;
use PrestaShop\Module\PrestashopCheckout\PayPal\PayPalConfiguration;
use PrestaShop\Module\PrestashopCheckout\Presenter\PresenterInterface;
use PrestaShop\Module\PrestashopCheckout\Shop\ShopProvider;
use PrestaShop\Module\PrestashopCheckout\ShopContext;
use PrestaShop\Module\PrestashopCheckout\Translations\Translations;

/**
 * Construct the context module
 */
class ContextModule implements PresenterInterface
{
    /**
     * @var string
     */
    private $moduleName;

    /**
     * @var string
     */
    private $moduleKey;

    /**
     * @var PrestaShopContext
     */
    private $psContext;

    /**
     * @var PayPalConfiguration
     */
    private $paypalConfiguration;

    /**
     * @var LiveStep
     */
    private $liveStep;

    /**
     * @var Translations
     */
    private $translations;

    /**
     * @var ShopContext
     */
    private $shopContext;

    /**
     * @var ShopProvider
     */
    private $shopProvider;

    /**
     * @param string $moduleName
     * @param string $moduleKey
     * @param PrestaShopContext $psContext
     * @param PayPalConfiguration $payPalConfiguration
     * @param LiveStep $liveStep
     * @param ValueBanner $valueBanner
     * @param Translations $translations
     * @param ShopContext $shopContext
     * @param ShopProvider $shopProvider
     */
    public function __construct(
        $moduleName,
        $moduleKey,
        PrestaShopContext $psContext,
        PayPalConfiguration $payPalConfiguration,
        LiveStep $liveStep,
        ValueBanner $valueBanner,
        Translations $translations,
        ShopContext $shopContext,
        ShopProvider $shopProvider
    ) {
        $this->moduleName = $moduleName;
        $this->moduleKey = $moduleKey;
        $this->psContext = $psContext;
        $this->paypalConfiguration = $payPalConfiguration;
        $this->liveStep = $liveStep;
        $this->valueBanner = $valueBanner;
        $this->translations = $translations;
        $this->shopContext = $shopContext;
        $this->shopProvider = $shopProvider;
    }

    /**
     * Present the context module (vuex)
     *
     * @return array
     */
    public function present()
    {
        $shopId = (int) \Context::getContext()->shop->id;

        /** @var \Ps_checkout $module */
        $module = \Module::getInstanceByName('ps_checkout');

        /** @var \PrestaShop\Module\PrestashopCheckout\Repository\PsAccountRepository $psAccountRepository */
        $psAccountRepository = $module->getService('ps_checkout.repository.prestashop.account');

        return [
            'context' => [
                'moduleVersion' => \Ps_checkout::VERSION,
                'psVersion' => _PS_VERSION_,
                'phpVersion' => phpversion(),
                'shopIs17' => $this->shopContext->isShop17(),
                'moduleKey' => $this->moduleKey,
                'shopId' => $psAccountRepository->getShopUuid(),
                'shopUri' => $this->shopProvider->getShopUrl($shopId),
                'isReady' => $this->shopContext->isReady(),
                'isShopContext' => $this->isShopContext(),
                'shopsTree' => $this->getShopsTree(),
                'faq' => $this->getFaq(),
                'language' => $this->psContext->getLanguage(),
                'prestashopCheckoutAjax' => (new LinkAdapter($this->psContext->getLink()))->getAdminLink('AdminAjaxPrestashopCheckout'),
                'translations' => $this->translations->getTranslations(),
                'readmeUrl' => $this->getReadme(),
                'cguUrl' => $this->getCgu(),
                'roundingSettingsIsCorrect' => $this->paypalConfiguration->IsRoundingSettingsCorrect(),
                'liveStepConfirmed' => $this->liveStep->isConfirmed(),
                'valueBannerClosed' => $this->valueBanner->isClosed(),
                'youtubeInstallerLink' => $this->getYoutubeInstallerLink(),
                'incompatibleCountryCodes' => $this->paypalConfiguration->getIncompatibleCountryCodes(),
                'incompatibleCurrencyCodes' => $this->paypalConfiguration->getIncompatibleCurrencyCodes(),
                'countriesLink' => $this->getGeneratedLink('AdminCountries'),
                'currenciesLink' => $this->getGeneratedLink('AdminCurrencies'),
                'paymentPreferencesLink' => $this->getGeneratedLink($this->shopContext->isShop17() ? 'AdminPaymentPreferences' : 'AdminPayment'),
            ],
        ];
    }

    /**
     * @return bool
     */
    private function isShopContext()
    {
        if (\Shop::isFeatureActive() && \Shop::getContext() !== \Shop::CONTEXT_SHOP) {
            return false;
        }

        return true;
    }

    /**
     * @return array
     */
    private function getShopsTree()
    {
        $shopList = [];

        if (true === $this->isShopContext()) {
            return $shopList;
        }

        $linkAdapter = new LinkAdapter($this->psContext->getLink());

        foreach (\Shop::getTree() as $groupId => $groupData) {
            $shops = [];

            foreach ($groupData['shops'] as $shopId => $shopData) {
                $shops[] = [
                    'id' => $shopId,
                    'name' => $shopData['name'],
                    'url' => $linkAdapter->getAdminLink(
                        'AdminModules',
                        true,
                        [],
                        [
                            'configure' => $this->moduleName,
                            'setShopContext' => 's-' . $shopId,
                        ]
                    ),
                ];
            }

            $shopList[] = [
                'id' => $groupId,
                'name' => $groupData['name'],
                'shops' => $shops,
            ];
        }

        return $shopList;
    }

    /**
     * Retrieve the faq
     *
     * @return array|bool faq or false if no faq associated to the module
     */
    private function getFaq()
    {
        $faq = new Faq();
        $faq->setModuleKey($this->moduleKey);
        $faq->setPsVersion(_PS_VERSION_);
        $faq->setIsoCode($this->psContext->getLanguageIsoCode());

        $response = $faq->getFaq();

        // If no response in the selected language, retrieve the faq in the default language (english)
        if (false === $response && $faq->getIsoCode() !== 'en') {
            $faq->setIsoCode('en');
            $response = $faq->getFaq();
        }

        return $response;
    }

    /**
     * Get the documentation url depending on the current language
     *
     * @return string path of the doc
     */
    private function getReadme()
    {
        $isoCode = $this->psContext->getLanguageIsoCode();

        $availableReadme = ['fr', 'en', 'it', 'es', 'nl', 'pl', 'pt'];

        if (!in_array($isoCode, $availableReadme)) {
            $isoCode = 'en';
        }

        return _MODULE_DIR_ . $this->moduleName . '/docs/readme_' . $isoCode . '.pdf';
    }

    /**
     * Get the CGU url
     *
     * @return string path of the doc
     */
    private function getCgu()
    {
        $isoCode = $this->psContext->getLanguageIsoCode();

        switch ($isoCode) {
            case 'fr':
                return 'https://www.prestashop.com/fr/prestashop-checkout-conditions-generales-utilisation';
            case 'es':
                return 'https://www.prestashop.com/es/prestashop-checkout-condiciones-generales-uso';
            case 'it':
                return 'https://www.prestashop.com/it/prestashop-checkout-condizioni-generali-utilizzo';
            default:
                return 'https://www.prestashop.com/en/prestashop-checkout-general-terms-use';
        }
    }

    /**
     * Get the youtube link to help people to install PS_Checkout
     *
     * @return string
     */
    private function getYoutubeInstallerLink()
    {
        $isoCode = $this->psContext->getLanguageIsoCode();
        $youtube = 'https://www.youtube.com/embed/';
        switch ($isoCode) {
            case 'fr':
                return $youtube . 'TVShtzk5eUM';
            case 'es':
                return $youtube . 'CjfhyR368Q0';
            case 'it':
                return $youtube . 'bBHuojwH2V8';
            default:
                return $youtube . 'uovtJVCLaD8';
        }
    }

    /**
     * Get the countries link
     *
     * @return string
     */
    private function getCountriesLink()
    {
        $linkAdapter = new LinkAdapter($this->psContext->getLink());

        return $linkAdapter->getAdminLink('AdminCountries');
    }

    /**
     * Get a generated link
     *
     * @param string $link
     *
     * @return string
     */
    public function getGeneratedLink($link)
    {
        $linkAdapter = new LinkAdapter($this->psContext->getLink());

        return $linkAdapter->getAdminLink($link);
    }
}

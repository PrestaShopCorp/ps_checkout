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
use PrestaShop\Module\PrestashopCheckout\Faq\Faq;
use PrestaShop\Module\PrestashopCheckout\Presenter\PresenterInterface;
use PrestaShop\Module\PrestashopCheckout\RoundingSettings\RoundingSettings;
use PrestaShop\Module\PrestashopCheckout\ShopContext;
use PrestaShop\Module\PrestashopCheckout\ShopUuidManager;
use PrestaShop\Module\PrestashopCheckout\Translations\Translations;

/**
 * Construct the context module
 */
class ContextModule implements PresenterInterface
{
    /**
     * @var \Module
     */
    private $module;

    /**
     * @var \Context
     */
    private $context;

    public function __construct(\Module $module, \Context $context)
    {
        $this->module = $module;
        $this->context = $context;
    }

    /**
     * Present the context module (vuex)
     *
     * @return array
     */
    public function present()
    {
        $contextModule = [
            'context' => [
                'moduleVersion' => \Ps_checkout::VERSION,
                'psVersion' => _PS_VERSION_,
                'phpVersion' => phpversion(),
                'shopIs17' => (new ShopContext())->isShop17(),
                'moduleKey' => $this->module->module_key,
                'shopId' => (new ShopUuidManager())->getForShop((int) \Context::getContext()->shop->id),
                'isReady' => (new ShopContext())->isReady(),
                'isShopContext' => $this->isShopContext(),
                'shopsTree' => $this->getShopsTree(),
                'faq' => $this->getFaq(),
                'language' => $this->context->language,
                'prestashopCheckoutAjax' => (new LinkAdapter($this->context->link))->getAdminLink('AdminAjaxPrestashopCheckout'),
                'translations' => (new Translations($this->module))->getTranslations(),
                'readmeUrl' => $this->getReadme(),
                'cguUrl' => $this->getCgu(),
                'roundingSettingsIsCorrect' => $this->module->getService('ps_checkout.paypal.configuration')->IsRoundingSettingsCorrect(),
                'liveStepConfirmed' => $this->module->getService('ps_checkout.step.live')->isConfirmed(),
                'youtubeInstallerLink' => $this->getYoutubeInstallerLink(),
            ],
        ];

        return $contextModule;
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

        $linkAdapter = new LinkAdapter($this->context->link);

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
                            'configure' => $this->module->name,
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
        $faq->setModuleKey($this->module->module_key);
        $faq->setPsVersion(_PS_VERSION_);
        $faq->setIsoCode($this->context->language->iso_code);

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
        $isoCode = $this->context->language->iso_code;

        $availableReadme = ['fr', 'en', 'it', 'es', 'nl', 'pl', 'pt'];

        if (!in_array($isoCode, $availableReadme)) {
            $isoCode = 'en';
        }

        return _MODULE_DIR_ . $this->module->name . '/docs/readme_' . $isoCode . '.pdf';
    }

    /**
     * Get the CGU url
     *
     * @return string path of the doc
     */
    private function getCgu()
    {
        $isoCode = $this->context->language->iso_code;

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
        $isoCode = $this->context->language->iso_code;
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
}

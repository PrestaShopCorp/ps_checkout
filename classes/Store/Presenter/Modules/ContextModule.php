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

use PrestaShop\Module\PrestashopCheckout\Faq\Faq;
use PrestaShop\Module\PrestashopCheckout\Translations\Translations;
use PrestaShop\Module\PrestashopCheckout\Store\Presenter\StorePresenterInterface;

/**
 * Construct the context module
 */
class ContextModule implements StorePresenterInterface
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
        $contextModule = array(
            'context' => array(
                'isReady' => $this->isReady(),
                'faq' => $this->getFaq(),
                'language' => $this->context->language,
                'prestashopCheckoutAjax' => $this->context->link->getAdminLink('AdminAjaxPrestashopCheckout'),
                'translations' => (new Translations($this->module))->getTranslations(),
                'readmeUrl' => $this->getReadme(),
            ),
        );

        return $contextModule;
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

        if ('fr' !== $isoCode) {
            $isoCode = 'en';
        }

        return _MODULE_DIR_ . $this->module->name . '/docs/readme_' . $isoCode . '.pdf';
    }

    /**
     * Check if the module is installed on ready or download
     *
     * @return bool true if on ready, false otherwise
     */
    private function isReady()
    {
        return getenv('PLATEFORM') === 'PSREADY';
    }
}

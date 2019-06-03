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

namespace PrestaShop\Module\PrestashopCheckout\Translations;

class Translations
{
    /**
     * @var \Module
     */
    private $module = null;

    public function __construct(\Module $module)
    {
        $this->module = $module;
    }

    /**
     * Create all tranlations (backoffice)
     *
     * @return array translation list
     */
    public function getTranslations()
    {
        // DOGE: In order to be unit testable, $locale should be sent as class or method param
        $locale = \Context::getContext()->language->locale;

        $translations[$locale] = array(
            'menu' => array(
                'authentication' => $this->module->l('Authentication'),
                'customizeCheckout' => $this->module->l('Customize checkout experience'),
                'manageActivity' => $this->module->l('Manage Activity'),
                'advancedSettings' => $this->module->l('Advanced settings'),
                'fees' => $this->module->l('Fees'),
                'help' => $this->module->l('Help'),
            ),
            'general' => array(
                'save' => $this->module->l('Save'),
            ),
            'auth' => array(
                'test' => $this->module->l('test'),
            ),
            'customize' => array(
                'test1' => $this->module->l('test2'),
            ),
            'manage' => array(
                'test' => $this->module->l('test'),
            ),
            'advanced' => array(
                'test' => $this->module->l('test'),
            ),
            'fees' => array(
                'test' => $this->module->l('test'),
            ),
            'help' => array(
                'test' => $this->module->l('test'),
            ),
        );

        return $translations;
    }
}

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

namespace PrestaShop\Module\PrestashopCheckout;

class HostedFieldsErrors
{
    /**
     * @var \Module
     */
    private $module = null;

    /**
     * @param \Module $module
     */
    public function __construct(\Module $module)
    {
        $this->module = $module;
    }

    /**
     * Return a list of errors code when a credit card is invalid with
     * the associated message
     *
     * @return string
     */
    public function getHostedFieldsErrors()
    {
        //TODO: Complete with all error code possible - waiting response from paypal
        $errors = array(
            'INVALID_STRING_LENGTH' => $this->module->l('Invalid format, check your bank details and try again'),
            'INVALID_EXPIRATION_YEAR' => $this->module->l('Expiration year must be between now and 2099'),
            'INVALID_PARAMETER_SYNTAX' => $this->module->l('Invalid format, check your bank details and try again'),
        );

        return json_encode($errors);
    }
}

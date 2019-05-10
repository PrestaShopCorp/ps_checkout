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

namespace PrestaShop\Module\PrestashopPayments;

class HostedFieldsErrors
{
    private $module = null;

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
            'INVALID_STRING_LENGTH' => $this->module->l('The value of a field is either too short or too long'),
            'INVALID_EXPIRATION_YEAR' => $this->module->l('Expiration Year must be between current year and 2099'),
            'INVALID_PARAMETER_SYNTAX' => $this->module->l('The value of a field does not conform to the expected format')
        );

        return json_encode($errors);
    }
}

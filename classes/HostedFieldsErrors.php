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
        $errors = [
            'INVALID_STRING_LENGTH' => $this->module->l('Invalid format, check your bank details and try again'),
            'INVALID_EXPIRATION_YEAR' => $this->module->l('Expiration year must be between now and 2099'),
            'INVALID_PARAMETER_SYNTAX' => $this->module->l('Invalid format, check your bank details and try again'),
            'TRANSACTION_NOT_SUPPORTED' => $this->module->l('This transaction is currently not supported. Please contact customer service or your account manager for more information.'),
        ];

        return json_encode($errors);
    }
}

<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

namespace PrestaShop\Module\PrestashopCheckout\Validator;

use PrestaShop\Module\PrestashopCheckout\Exception\PayPalException;

class PayloadBuilderValidator
{
    private $validCurrencies = ['EUR', 'USD', 'CHF', 'GBP'];

    public function checkNodeValues($node)
    {
        if ($node['intent'] != 'CAPTURE' && $node['intent'] != 'AUTHORIZE') {
            throw new PayPalException(sprintf('Passed intent %s is unsupported', $node['intent']), PayPalException::UNSUPPORTED_INTENT);
        }
        if (!in_array($node['amount']['currency_code'], $this->validCurrencies)) {
            throw new PayPalException(sprintf('Passed currency %s is invalid', $node['amount']['currency_code']), PayPalException::INVALID_CURRENCY_CODE);
        }
        if ($node['amount']['value'] <= 0) {
            throw new PayPalException(sprintf('Passed amount %s is less or equal to zero', $node['amount']['value']), PayPalException::AMOUNT_MISMATCH);
        }
        if (empty($node['payee']['merchant_id'])) {
            throw new PayPalException(sprintf('Passed merchant id %s is invalid', $node['payee']['merchant_id']), PayPalException::PAYEE_ACCOUNT_NOT_SUPPORTED);
        }
    }
}

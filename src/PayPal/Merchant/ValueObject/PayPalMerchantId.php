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

namespace PrestaShop\Module\PrestashopCheckout\PayPal\Merchant\ValueObject;

use PrestaShop\Module\PrestashopCheckout\PayPal\Merchant\Exception\PayPalMerchantException;

class PayPalMerchantId
{
    /**
     * @var string
     */
    private $value;

    /**
     * @param string $value
     *
     * @throws PayPalMerchantException
     */
    public function __construct($value)
    {
        $this->assertStringIsMatchingRegex($value);

        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param string $value
     *
     * @throws PayPalMerchantException
     */
    public function assertStringIsMatchingRegex($value)
    {
        if (!is_string($value) || !preg_match('/^[0-9A-Z]+$/', $value)) {
            throw new PayPalMerchantException(sprintf('PayPal merchant id %s is invalid. PayPal merchant id must be an alphanumeric string.', var_export($value, true)), PayPalMerchantException::INVALID_ID);
        }
    }
}

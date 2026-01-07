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

namespace PsCheckout\Api\Dto\PayPal;

/**
 * The exchange rate that determines the amount to convert from one currency to another currency.
 */
class ExchangeRate
{
    /**
     * @var string|null
     */
    private $sourceCurrency;

    /**
     * @var string|null
     */
    private $targetCurrency;

    /**
     * @var string|null
     */
    private $value;

    /**
     * Returns Source Currency.
     * The [three-character ISO-4217 currency code](/api/rest/reference/currency-codes/) that identifies
     * the currency.
     */
    public function getSourceCurrency(): ?string
    {
        return $this->sourceCurrency;
    }

    /**
     * Sets Source Currency.
     * The [three-character ISO-4217 currency code](/api/rest/reference/currency-codes/) that identifies
     * the currency.
     *
     * @maps source_currency
     * @return self
     */
    public function setSourceCurrency(?string $sourceCurrency): self
    {
        $this->sourceCurrency = $sourceCurrency;

        return $this;
    }

    /**
     * Returns Target Currency.
     * The [three-character ISO-4217 currency code](/api/rest/reference/currency-codes/) that identifies
     * the currency.
     */
    public function getTargetCurrency(): ?string
    {
        return $this->targetCurrency;
    }

    /**
     * Sets Target Currency.
     * The [three-character ISO-4217 currency code](/api/rest/reference/currency-codes/) that identifies
     * the currency.
     *
     * @maps target_currency
     * @return self
     */
    public function setTargetCurrency(?string $targetCurrency): self
    {
        $this->targetCurrency = $targetCurrency;

        return $this;
    }

    /**
     * Returns Value.
     * The target currency amount. Equivalent to one unit of the source currency. Formatted as integer or
     * decimal value with one to 15 digits to the right of the decimal point.
     */
    public function getValue(): ?string
    {
        return $this->value;
    }

    /**
     * Sets Value.
     * The target currency amount. Equivalent to one unit of the source currency. Formatted as integer or
     * decimal value with one to 15 digits to the right of the decimal point.
     *
     * @maps value
     * @return self
     */
    public function setValue(?string $value): self
    {
        $this->value = $value;

        return $this;
    }
}

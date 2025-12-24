<?php

namespace PsCheckout\Api\Dto\PayPal;

class Money
{
    /**
     * @var string
     */
    private $currencyCode;

    /**
     * @var string
     */
    private $value;

    /**
     * @param string $currencyCode
     * @param string $value
     */
    public function __construct(string $currencyCode, string $value)
    {
        $this->currencyCode = $currencyCode;
        $this->value = $value;
    }

    /**
     * Returns Currency Code.
     * The [three-character ISO-4217 currency code](/api/rest/reference/currency-codes/) that identifies
     * the currency.
     */
    public function getCurrencyCode(): string
    {
        return $this->currencyCode;
    }

    /**
     * Sets Currency Code.
     * The [three-character ISO-4217 currency code](/api/rest/reference/currency-codes/) that identifies
     * the currency.
     *
     * @required
     * @maps currency_code
     */
    public function setCurrencyCode(string $currencyCode): void
    {
        $this->currencyCode = $currencyCode;
    }

    /**
     * Returns Value.
     * The value, which might be: An integer for currencies like `JPY` that are not typically fractional. A
     * decimal fraction for currencies like `TND` that are subdivided into thousandths. For the required
     * number of decimal places for a currency code, see [Currency Codes](/api/rest/reference/currency-
     * codes/).
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * Sets Value.
     * The value, which might be: An integer for currencies like `JPY` that are not typically fractional. A
     * decimal fraction for currencies like `TND` that are subdivided into thousandths. For the required
     * number of decimal places for a currency code, see [Currency Codes](/api/rest/reference/currency-
     * codes/).
     *
     * @required
     * @maps value
     */
    public function setValue(string $value): void
    {
        $this->value = $value;
    }
}

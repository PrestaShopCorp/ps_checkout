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

namespace PrestaShop\Module\PrestashopCheckout\PayPal\Order\DTO;

class ProcessorResponse
{
    /**
     * The address verification code for Visa, Discover, Mastercard, or American Express transactions.
     *
     * @var string|null
     */
    protected $avs_code;

    /**
     * The card verification value code for for Visa, Discover, Mastercard, or American Express.
     *
     * @var string|null
     */
    protected $cvv_code;

    /**
     * Processor response code for the non-PayPal payment processor errors.
     *
     * @var string|null
     */
    protected $response_code;

    /**
     * The declined payment transactions might have payment advice codes. The card networks, like Visa and Mastercard, return payment advice codes.
     *
     * @var string|null
     */
    protected $payment_advice_code;

    /**
     * Constructor
     *
     * @param mixed[] $data Associated array of property values initializing the model
     */
    public function __construct(array $data = null)
    {
        $this->avs_code = isset($data['avs_code']) ? $data['avs_code'] : null;
        $this->cvv_code = isset($data['cvv_code']) ? $data['cvv_code'] : null;
        $this->response_code = isset($data['response_code']) ? $data['response_code'] : null;
        $this->payment_advice_code = isset($data['payment_advice_code']) ? $data['payment_advice_code'] : null;
    }

    /**
     * Gets avs_code.
     *
     * @return string|null
     */
    public function getAvsCode()
    {
        return $this->avs_code;
    }

    /**
     * Sets avs_code.
     *
     * @param string|null $avs_code the address verification code for Visa, Discover, Mastercard, or American Express transactions
     *
     * @return $this
     */
    public function setAvsCode($avs_code = null)
    {
        $this->avs_code = $avs_code;

        return $this;
    }

    /**
     * Gets cvv_code.
     *
     * @return string|null
     */
    public function getCvvCode()
    {
        return $this->cvv_code;
    }

    /**
     * Sets cvv_code.
     *
     * @param string|null $cvv_code the card verification value code for Visa, Discover, Mastercard, or American Express
     *
     * @return $this
     */
    public function setCvvCode($cvv_code = null)
    {
        $this->cvv_code = $cvv_code;

        return $this;
    }

    /**
     * Gets response_code.
     *
     * @return string|null
     */
    public function getResponseCode()
    {
        return $this->response_code;
    }

    /**
     * Sets response_code.
     *
     * @param string|null $response_code processor response code for the non-PayPal payment processor errors
     *
     * @return $this
     */
    public function setResponseCode($response_code = null)
    {
        $this->response_code = $response_code;

        return $this;
    }

    /**
     * Gets payment_advice_code.
     *
     * @return string|null
     */
    public function getPaymentAdviceCode()
    {
        return $this->payment_advice_code;
    }

    /**
     * Sets payment_advice_code.
     *
     * @param string|null $payment_advice_code The declined payment transactions might have payment advice codes. The card networks, like Visa and Mastercard, return payment advice codes.
     *
     * @return $this
     */
    public function setPaymentAdviceCode($payment_advice_code = null)
    {
        $this->payment_advice_code = $payment_advice_code;

        return $this;
    }
}

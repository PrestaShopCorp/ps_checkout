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
 * The processor response information for payment requests, such as direct credit card transactions.
 */
class ProcessorResponse
{
    /**
     * @var string|null
     */
    private $avsCode;

    /**
     * @var string|null
     */
    private $cvvCode;

    /**
     * @var string|null
     */
    private $responseCode;

    /**
     * @var string|null
     */
    private $paymentAdviceCode;

    /**
     * Returns Avs Code.
     * The address verification code for Visa, Discover, Mastercard, or American Express transactions.
     */
    public function getAvsCode(): ?string
    {
        return $this->avsCode;
    }

    /**
     * Sets Avs Code.
     * The address verification code for Visa, Discover, Mastercard, or American Express transactions.
     *
     * @maps avs_code
     * @return self
     */
    public function setAvsCode(?string $avsCode): self
    {
        $this->avsCode = $avsCode;

        return $this;
    }

    /**
     * Returns Cvv Code.
     * The card verification value code for for Visa, Discover, Mastercard, or American Express.
     */
    public function getCvvCode(): ?string
    {
        return $this->cvvCode;
    }

    /**
     * Sets Cvv Code.
     * The card verification value code for for Visa, Discover, Mastercard, or American Express.
     *
     * @maps cvv_code
     * @return self
     */
    public function setCvvCode(?string $cvvCode): self
    {
        $this->cvvCode = $cvvCode;

        return $this;
    }

    /**
     * Returns Response Code.
     * Processor response code for the non-PayPal payment processor errors.
     */
    public function getResponseCode(): ?string
    {
        return $this->responseCode;
    }

    /**
     * Sets Response Code.
     * Processor response code for the non-PayPal payment processor errors.
     *
     * @maps response_code
     * @return self
     */
    public function setResponseCode(?string $responseCode): self
    {
        $this->responseCode = $responseCode;

        return $this;
    }

    /**
     * Returns Payment Advice Code.
     * The declined payment transactions might have payment advice codes. The card networks, like Visa and
     * Mastercard, return payment advice codes.
     */
    public function getPaymentAdviceCode(): ?string
    {
        return $this->paymentAdviceCode;
    }

    /**
     * Sets Payment Advice Code.
     * The declined payment transactions might have payment advice codes. The card networks, like Visa and
     * Mastercard, return payment advice codes.
     *
     * @maps payment_advice_code
     * @return self
     */
    public function setPaymentAdviceCode(?string $paymentAdviceCode): self
    {
        $this->paymentAdviceCode = $paymentAdviceCode;

        return $this;
    }
}

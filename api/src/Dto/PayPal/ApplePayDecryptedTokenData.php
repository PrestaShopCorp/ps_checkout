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
 * Information about the Payment data obtained by decrypting Apple Pay token.
 */
class ApplePayDecryptedTokenData
{
    /**
     * @var Money|null
     */
    private $transactionAmount;

    /**
     * @var ApplePayTokenizedCard
     */
    private $tokenizedCard;

    /**
     * @var string|null
     */
    private $deviceManufacturerId;

    /**
     * @var string|null
     */
    private $paymentDataType;

    /**
     * @var ApplePayPaymentData|null
     */
    private $paymentData;

    /**
     * @param ApplePayTokenizedCard $tokenizedCard
     */
    public function __construct(ApplePayTokenizedCard $tokenizedCard)
    {
        $this->tokenizedCard = $tokenizedCard;
    }

    /**
     * Returns Transaction Amount.
     * The currency and amount for a financial transaction, such as a balance or payment due.
     */
    public function getTransactionAmount(): ?Money
    {
        return $this->transactionAmount;
    }

    /**
     * Sets Transaction Amount.
     * The currency and amount for a financial transaction, such as a balance or payment due.
     *
     * @maps transaction_amount
     */
    public function setTransactionAmount(?Money $transactionAmount): void
    {
        $this->transactionAmount = $transactionAmount;
    }

    /**
     * Returns Tokenized Card.
     * The payment card to use to fund a payment. Can be a credit or debit card.
     */
    public function getTokenizedCard(): ApplePayTokenizedCard
    {
        return $this->tokenizedCard;
    }

    /**
     * Sets Tokenized Card.
     * The payment card to use to fund a payment. Can be a credit or debit card.
     *
     * @required
     * @maps tokenized_card
     */
    public function setTokenizedCard(ApplePayTokenizedCard $tokenizedCard): void
    {
        $this->tokenizedCard = $tokenizedCard;
    }

    /**
     * Returns Device Manufacturer Id.
     * Apple Pay Hex-encoded device manufacturer identifier. The pattern is defined by an external party
     * and supports Unicode.
     */
    public function getDeviceManufacturerId(): ?string
    {
        return $this->deviceManufacturerId;
    }

    /**
     * Sets Device Manufacturer Id.
     * Apple Pay Hex-encoded device manufacturer identifier. The pattern is defined by an external party
     * and supports Unicode.
     *
     * @maps device_manufacturer_id
     */
    public function setDeviceManufacturerId(?string $deviceManufacturerId): void
    {
        $this->deviceManufacturerId = $deviceManufacturerId;
    }

    /**
     * Returns Payment Data Type.
     * Indicates the type of payment data passed, in case of Non China the payment data is 3DSECURE and for
     * China it is EMV.
     */
    public function getPaymentDataType(): ?string
    {
        return $this->paymentDataType;
    }

    /**
     * Sets Payment Data Type.
     * Indicates the type of payment data passed, in case of Non China the payment data is 3DSECURE and for
     * China it is EMV.
     *
     * @maps payment_data_type
     */
    public function setPaymentDataType(?string $paymentDataType): void
    {
        $this->paymentDataType = $paymentDataType;
    }

    /**
     * Returns Payment Data.
     * Information about the decrypted apple pay payment data for the token like cryptogram, eci indicator.
     */
    public function getPaymentData(): ?ApplePayPaymentData
    {
        return $this->paymentData;
    }

    /**
     * Sets Payment Data.
     * Information about the decrypted apple pay payment data for the token like cryptogram, eci indicator.
     *
     * @maps payment_data
     */
    public function setPaymentData(?ApplePayPaymentData $paymentData): void
    {
        $this->paymentData = $paymentData;
    }
}

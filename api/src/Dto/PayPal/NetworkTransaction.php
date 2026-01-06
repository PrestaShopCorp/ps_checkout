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
 * Reference values used by the card network to identify a transaction.
 */
class NetworkTransaction
{
    /**
     * @var string|null
     */
    private $id;

    /**
     * @var string|null
     */
    private $date;

    /**
     * @var string|null
     */
    private $network;

    /**
     * @var string|null
     */
    private $acquirerReferenceNumber;

    /**
     * Returns Id.
     * Transaction reference id returned by the scheme. For Visa and Amex, this is the "Tran id" field in
     * response. For MasterCard, this is the "BankNet reference id" field in response. For Discover, this
     * is the "NRID" field in response. The pattern we expect for this field from Visa/Amex/CB/Discover is
     * numeric, Mastercard/BNPP is alphanumeric and Paysecure is alphanumeric with special character -.
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * Sets Id.
     * Transaction reference id returned by the scheme. For Visa and Amex, this is the "Tran id" field in
     * response. For MasterCard, this is the "BankNet reference id" field in response. For Discover, this
     * is the "NRID" field in response. The pattern we expect for this field from Visa/Amex/CB/Discover is
     * numeric, Mastercard/BNPP is alphanumeric and Paysecure is alphanumeric with special character -.
     *
     * @maps id
     */
    public function setId(?string $id): void
    {
        $this->id = $id;
    }

    /**
     * Returns Date.
     * The date that the transaction was authorized by the scheme. This field may not be returned for all
     * networks. MasterCard refers to this field as "BankNet reference date". For some specific networks,
     * such as MasterCard and Discover, this date field is mandatory when the
     * `previous_network_transaction_reference_id` is passed.
     */
    public function getDate(): ?string
    {
        return $this->date;
    }

    /**
     * Sets Date.
     * The date that the transaction was authorized by the scheme. This field may not be returned for all
     * networks. MasterCard refers to this field as "BankNet reference date". For some specific networks,
     * such as MasterCard and Discover, this date field is mandatory when the
     * `previous_network_transaction_reference_id` is passed.
     *
     * @maps date
     */
    public function setDate(?string $date): void
    {
        $this->date = $date;
    }

    /**
     * Returns Network.
     * The card network or brand. Applies to credit, debit, gift, and payment cards.
     */
    public function getNetwork(): ?string
    {
        return $this->network;
    }

    /**
     * Sets Network.
     * The card network or brand. Applies to credit, debit, gift, and payment cards.
     *
     * @maps network
     */
    public function setNetwork(?string $network): void
    {
        $this->network = $network;
    }

    /**
     * Returns Acquirer Reference Number.
     * Reference ID issued for the card transaction. This ID can be used to track the transaction across
     * processors, card brands and issuing banks.
     */
    public function getAcquirerReferenceNumber(): ?string
    {
        return $this->acquirerReferenceNumber;
    }

    /**
     * Sets Acquirer Reference Number.
     * Reference ID issued for the card transaction. This ID can be used to track the transaction across
     * processors, card brands and issuing banks.
     *
     * @maps acquirer_reference_number
     */
    public function setAcquirerReferenceNumber(?string $acquirerReferenceNumber): void
    {
        $this->acquirerReferenceNumber = $acquirerReferenceNumber;
    }
}

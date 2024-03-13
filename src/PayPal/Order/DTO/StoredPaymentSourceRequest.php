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

class StoredPaymentSourceRequest
{
    /**
     * @var string
     */
    private $payment_initiator;
    /**
     * @var string
     */
    private $payment_type;
    /**
     * @var string
     */
    private $usage;
    /**
     * @var PreviousNetworkTransactionReferenceRequest
     */
    private $previous_network_transaction_reference;

    /**
     * @return string
     */
    public function getPaymentInitiator()
    {
        return $this->payment_initiator;
    }

    /**
     * @param string $payment_initiator
     *
     * @return self
     */
    public function setPaymentInitiator($payment_initiator)
    {
        $this->payment_initiator = $payment_initiator;

        return $this;
    }

    /**
     * @return string
     */
    public function getPaymentType()
    {
        return $this->payment_type;
    }

    /**
     * @param string $payment_type
     *
     * @return self
     */
    public function setPaymentType($payment_type)
    {
        $this->payment_type = $payment_type;

        return $this;
    }

    /**
     * @return string
     */
    public function getUsage()
    {
        return $this->usage;
    }

    /**
     * @param string $usage
     *
     * @return self
     */
    public function setUsage($usage)
    {
        $this->usage = $usage;

        return $this;
    }

    /**
     * @return PreviousNetworkTransactionReferenceRequest
     */
    public function getPreviousNetworkTransactionReference()
    {
        return $this->previous_network_transaction_reference;
    }

    /**
     * @param PreviousNetworkTransactionReferenceRequest $previous_network_transaction_reference
     *
     * @return self
     */
    public function setPreviousNetworkTransactionReference(PreviousNetworkTransactionReferenceRequest $previous_network_transaction_reference)
    {
        $this->previous_network_transaction_reference = $previous_network_transaction_reference;

        return $this;
    }
}

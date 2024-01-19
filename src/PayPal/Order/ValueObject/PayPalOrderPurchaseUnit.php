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

namespace PrestaShop\Module\PrestashopCheckout\PayPal\Order\ValueObject;

class PayPalOrderPurchaseUnit
{
    /**
     * @var string
     */
    private $referenceId;
    /**
     * @var string
     */
    private $currency;
    /**
     * @var string
     */
    private $value;
    /**
     * @var string
     */
    private $customId;

    public function __construct($referenceId, $customId, $currency, $value)
    {
        $this->referenceId = $referenceId;
        $this->currency = $currency;
        $this->value = $value;
        $this->customId = $customId;
    }

    /**
     * @return string
     */
    public function getReferenceId()
    {
        return $this->referenceId;
    }

    /**
     * @param string $referenceId
     */
    public function setReferenceId($referenceId)
    {
        $this->referenceId = $referenceId;
    }

    /**
     * @return string
     */
    public function getCustomId()
    {
        return $this->customId;
    }

    /**
     * @param string $customId
     */
    public function setCustomId($customId)
    {
        $this->customId = $customId;
    }

    /**
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @param mixed $currency
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    public function toArray()
    {
        return [
            'reference_id' => $this->referenceId,
            'custom_id' => $this->customId,
            'amount' => [
                'currency' => $this->currency,
                'value' => $this->value,
            ],
        ];
    }
}

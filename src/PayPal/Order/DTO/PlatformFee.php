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

class PlatformFee
{
    /**
     * @var Amount
     */
    protected $amount;

    /**
     * @var Payee|null
     */
    protected $payee;

    /**
     * Constructor
     *
     * @param mixed[] $data Associated array of property values initializing the model
     */
    public function __construct(array $data = null)
    {
        $this->amount = isset($data['amount']) ? $data['amount'] : null;
        $this->payee = isset($data['payee']) ? $data['payee'] : null;
    }

    /**
     * Gets amount.
     *
     * @return Amount
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Sets amount.
     *
     * @param Amount $amount
     *
     * @return $this
     */
    public function setAmount(Amount $amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Gets payee.
     *
     * @return Payee|null
     */
    public function getPayee()
    {
        return $this->payee;
    }

    /**
     * Sets payee.
     *
     * @param Payee|null $payee
     *
     * @return $this
     */
    public function setPayee(Payee $payee = null)
    {
        $this->payee = $payee;

        return $this;
    }
}

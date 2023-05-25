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

namespace PrestaShop\Module\PrestashopCheckout\Order\Query;

class GetOrderForPaymentCompletedQueryResult
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var int
     */
    private $currentState;

    /**
     * @var bool
     */
    private $hasBeenPaid;

    /**
     * @var string
     */
    private $totalAmount;

    /**
     * @var string
     */
    private $totalAmountPaid;

    /**
     * @var int
     */
    private $currencyId;

    /**
     * @param int $id
     * @param int $currentState
     * @param bool $hasBeenPaid
     * @param string $totalAmount
     * @param string $totalAmountPaid
     * @param int $currencyId
     */
    public function __construct(
        $id,
        $currentState,
        $hasBeenPaid,
        $totalAmount,
        $totalAmountPaid,
        $currencyId
    ) {
        $this->id = $id;
        $this->currentState = $currentState;
        $this->hasBeenPaid = $hasBeenPaid;
        $this->totalAmount = $totalAmount;
        $this->totalAmountPaid = $totalAmountPaid;
        $this->currencyId = $currencyId;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getCurrentStateId()
    {
        return $this->currentState;
    }

    /**
     * @return bool
     */
    public function hasBeenPaid()
    {
        return $this->hasBeenPaid;
    }

    /**
     * @return string
     */
    public function getTotalAmount()
    {
        return $this->totalAmount;
    }

    /**
     * @return string
     */
    public function getTotalAmountPaid()
    {
        return $this->totalAmountPaid;
    }

    /**
     * @return int
     */
    public function getCurrencyId()
    {
        return $this->currencyId;
    }
}

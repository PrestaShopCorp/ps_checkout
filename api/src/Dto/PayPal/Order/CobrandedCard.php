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

namespace PsCheckout\Api\Dto\PayPal\Order;

use PsCheckout\Api\Dto\PayPal\Money;
use PsCheckout\Api\Dto\PayPal\PayeeBase;

/**
 * Details about the merchant cobranded card used for order purchase.
 */
class CobrandedCard
{
    /**
     * @var string[]|null
     */
    private $labels;

    /**
     * @var PayeeBase|null
     */
    private $payee;

    /**
     * @var Money|null
     */
    private $amount;

    /**
     * Returns Labels.
     * Array of labels for the cobranded card.
     *
     * @return string[]|null
     */
    public function getLabels(): ?array
    {
        return $this->labels;
    }

    /**
     * Sets Labels.
     * Array of labels for the cobranded card.
     *
     * @maps labels
     *
     * @param string[]|null $labels
     */
    public function setLabels(?array $labels): void
    {
        $this->labels = $labels;
    }

    /**
     * Returns Payee.
     * The details for the merchant who receives the funds and fulfills the order. The merchant is also
     * known as the payee.
     */
    public function getPayee(): ?PayeeBase
    {
        return $this->payee;
    }

    /**
     * Sets Payee.
     * The details for the merchant who receives the funds and fulfills the order. The merchant is also
     * known as the payee.
     *
     * @maps payee
     */
    public function setPayee(?PayeeBase $payee): void
    {
        $this->payee = $payee;
    }

    /**
     * Returns Amount.
     * The currency and amount for a financial transaction, such as a balance or payment due.
     */
    public function getAmount(): ?Money
    {
        return $this->amount;
    }

    /**
     * Sets Amount.
     * The currency and amount for a financial transaction, such as a balance or payment due.
     *
     * @maps amount
     */
    public function setAmount(?Money $amount): void
    {
        $this->amount = $amount;
    }
}

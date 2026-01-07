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

namespace PsCheckout\Api\Dto\PayPal\Payment;

use PsCheckout\Api\Dto\PayPal\Money;

/**
 * Reauthorizes an authorized PayPal account payment, by ID. To ensure that funds are still available,
 * reauthorize a payment after its initial three-day honor period expires. You can reauthorize a
 * payment only once from days four to 29. If 30 days have transpired since the date of the original
 * authorization, you must create an authorized payment instead of reauthorizing the original
 * authorized payment. A reauthorized payment itself has a new honor period of three days. You can
 * reauthorize an authorized payment once. The allowed amount depends on context and geography, for
 * example in US it is up to 115% of the original authorized amount, not to exceed an increase of $75
 * USD. Supports only the `amount` request parameter.
 */
class ReauthorizeRequestDto
{
    /**
     * @var Money|null
     */
    private $amount;

    public function __construct(?Money $amount = null)
    {
        $this->amount = $amount;
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
     * @return self
     */
    public function setAmount(?Money $amount): self
    {
        $this->amount = $amount;

        return $this;
    }
}

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
 * The collection of payments, or transactions, for a purchase unit in an order. For example,
 * authorized payments, captured payments, and refunds.
 */
class PaymentCollection
{
    /**
     * @var AuthorizationWithAdditionalData[]|null
     */
    private $authorizations;

    /**
     * @var OrdersCapture[]|null
     */
    private $captures;

    /**
     * @var Refund[]|null
     */
    private $refunds;

    /**
     * Returns Authorizations.
     * An array of authorized payments for a purchase unit. A purchase unit can have zero or more
     * authorized payments.
     *
     * @return AuthorizationWithAdditionalData[]|null
     */
    public function getAuthorizations(): ?array
    {
        return $this->authorizations;
    }

    /**
     * Sets Authorizations.
     * An array of authorized payments for a purchase unit. A purchase unit can have zero or more
     * authorized payments.
     *
     * @maps authorizations
     *
     * @param AuthorizationWithAdditionalData[]|null $authorizations
     * @return self
     */
    public function setAuthorizations(?array $authorizations): self
    {
        $this->authorizations = $authorizations;

        return $this;
    }

    /**
     * Returns Captures.
     * An array of captured payments for a purchase unit. A purchase unit can have zero or more captured
     * payments.
     *
     * @return OrdersCapture[]|null
     */
    public function getCaptures(): ?array
    {
        return $this->captures;
    }

    /**
     * Sets Captures.
     * An array of captured payments for a purchase unit. A purchase unit can have zero or more captured
     * payments.
     *
     * @maps captures
     *
     * @param OrdersCapture[]|null $captures
     * @return self
     */
    public function setCaptures(?array $captures): self
    {
        $this->captures = $captures;

        return $this;
    }

    /**
     * Returns Refunds.
     * An array of refunds for a purchase unit. A purchase unit can have zero or more refunds.
     *
     * @return Refund[]|null
     */
    public function getRefunds(): ?array
    {
        return $this->refunds;
    }

    /**
     * Sets Refunds.
     * An array of refunds for a purchase unit. A purchase unit can have zero or more refunds.
     *
     * @maps refunds
     *
     * @param Refund[]|null $refunds
     * @return self
     */
    public function setRefunds(?array $refunds): self
    {
        $this->refunds = $refunds;

        return $this;
    }
}

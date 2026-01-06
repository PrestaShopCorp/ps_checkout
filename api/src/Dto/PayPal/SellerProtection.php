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
 * The level of protection offered as defined by [PayPal Seller Protection for Merchants](https://www.
 * paypal.com/us/webapps/mpp/security/seller-protection).
 */
class SellerProtection
{
    /**
     * @var string|null
     */
    private $status;

    /**
     * @var string[]|null
     */
    private $disputeCategories;

    /**
     * Returns Status.
     * Indicates whether the transaction is eligible for seller protection. For information, see [PayPal
     * Seller Protection for Merchants](https://www.paypal.com/us/webapps/mpp/security/seller-protection).
     */
    public function getStatus(): ?string
    {
        return $this->status;
    }

    /**
     * Sets Status.
     * Indicates whether the transaction is eligible for seller protection. For information, see [PayPal
     * Seller Protection for Merchants](https://www.paypal.com/us/webapps/mpp/security/seller-protection).
     *
     * @maps status
     */
    public function setStatus(?string $status): void
    {
        $this->status = $status;
    }

    /**
     * Returns Dispute Categories.
     * An array of conditions that are covered for the transaction.
     *
     * @return string[]|null
     */
    public function getDisputeCategories(): ?array
    {
        return $this->disputeCategories;
    }

    /**
     * Sets Dispute Categories.
     * An array of conditions that are covered for the transaction.
     *
     * @maps dispute_categories
     *
     * @param string[]|null $disputeCategories
     */
    public function setDisputeCategories(?array $disputeCategories): void
    {
        $this->disputeCategories = $disputeCategories;
    }
}

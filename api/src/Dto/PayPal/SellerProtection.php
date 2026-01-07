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

use PsCheckout\Api\Dto\PayPal\SellerProtectionStatus;
use PsCheckout\Api\Dto\PayPal\DisputeCategory;

/**
 * The level of protection offered as defined by [PayPal Seller Protection for Merchants](https://www.
 * paypal.com/us/webapps/mpp/security/seller-protection).
 */
class SellerProtection
{
    /**
     * @var value-of<SellerProtectionStatus::STATUSES>|null
     */
    private $status;

    /**
     * @var value-of<DisputeCategory::CATEGORIES>[]|null
     */
    private $disputeCategories;

    /**
     * Returns Status.
     * Indicates whether the transaction is eligible for seller protection. For information, see [PayPal
     * Seller Protection for Merchants](https://www.paypal.com/us/webapps/mpp/security/seller-protection).
     *
     * @return value-of<SellerProtectionStatus::STATUSES>|null
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
     *
     * @param value-of<SellerProtectionStatus::STATUSES>|null $status
     *
     * @return self
     */
    public function setStatus(?string $status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Returns Dispute Categories.
     * An array of conditions that are covered for the transaction.
     *
     * @return value-of<DisputeCategory::CATEGORIES>[]|null
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
     * @param value-of<DisputeCategory::CATEGORIES>[]|null $disputeCategories
     *
     * @return self
     */
    public function setDisputeCategories(?array $disputeCategories): self
    {
        $this->disputeCategories = $disputeCategories;

        return $this;
    }
}

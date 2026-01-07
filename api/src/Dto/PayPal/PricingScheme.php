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
 * The pricing scheme details.
 */
class PricingScheme
{
    /**
     * @var Money|null
     */
    private $price;

    /**
     * @var string
     */
    private $pricingModel;

    /**
     * @var Money|null
     */
    private $reloadThresholdAmount;

    /**
     * @param string $pricingModel
     */
    public function __construct(string $pricingModel)
    {
        $this->pricingModel = $pricingModel;
    }

    /**
     * Returns Price.
     * The currency and amount for a financial transaction, such as a balance or payment due.
     */
    public function getPrice(): ?Money
    {
        return $this->price;
    }

    /**
     * Sets Price.
     * The currency and amount for a financial transaction, such as a balance or payment due.
     *
     * @maps price
     */
    public function setPrice(?Money $price): void
    {
        $this->price = $price;
    }

    /**
     * Returns Pricing Model.
     * The pricing model for the billing cycle.
     */
    public function getPricingModel(): string
    {
        return $this->pricingModel;
    }

    /**
     * Sets Pricing Model.
     * The pricing model for the billing cycle.
     *
     * @required
     * @maps pricing_model
     */
    public function setPricingModel(string $pricingModel): void
    {
        $this->pricingModel = $pricingModel;
    }

    /**
     * Returns Reload Threshold Amount.
     * The currency and amount for a financial transaction, such as a balance or payment due.
     */
    public function getReloadThresholdAmount(): ?Money
    {
        return $this->reloadThresholdAmount;
    }

    /**
     * Sets Reload Threshold Amount.
     * The currency and amount for a financial transaction, such as a balance or payment due.
     *
     * @maps reload_threshold_amount
     */
    public function setReloadThresholdAmount(?Money $reloadThresholdAmount): void
    {
        $this->reloadThresholdAmount = $reloadThresholdAmount;
    }
}

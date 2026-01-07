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
 * The level 2 card processing data collections. If your merchant account has been configured for Level
 * 2 processing this field will be passed to the processor on your behalf. Please contact your PayPal
 * Technical Account Manager to define level 2 data for your business.
 */
class Level2CardProcessingData
{
    /**
     * @var string|null
     */
    private $invoiceId;

    /**
     * @var Money|null
     */
    private $taxTotal;

    /**
     * Returns Invoice Id.
     * Use this field to pass a purchase identification value of up to 127 ASCII characters. The length of
     * this field will be adjusted to meet network specifications (25chars for Visa and Mastercard, 17chars
     * for Amex), and the original invoice ID will still be displayed in your existing reports.
     */
    public function getInvoiceId(): ?string
    {
        return $this->invoiceId;
    }

    /**
     * Sets Invoice Id.
     * Use this field to pass a purchase identification value of up to 127 ASCII characters. The length of
     * this field will be adjusted to meet network specifications (25chars for Visa and Mastercard, 17chars
     * for Amex), and the original invoice ID will still be displayed in your existing reports.
     *
     * @maps invoice_id
     * @return self
     */
    public function setInvoiceId(?string $invoiceId): self
    {
        $this->invoiceId = $invoiceId;

        return $this;
    }

    /**
     * Returns Tax Total.
     * The currency and amount for a financial transaction, such as a balance or payment due.
     */
    public function getTaxTotal(): ?Money
    {
        return $this->taxTotal;
    }

    /**
     * Sets Tax Total.
     * The currency and amount for a financial transaction, such as a balance or payment due.
     *
     * @maps tax_total
     * @return self
     */
    public function setTaxTotal(?Money $taxTotal): self
    {
        $this->taxTotal = $taxTotal;

        return $this;
    }
}

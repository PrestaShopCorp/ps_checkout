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

class Level2CardProcessingData
{
    /**
     * Use this field to pass a purchase identification value of up to 12 ASCII characters for AIB and 17 ASCII characters for all other processors.
     *
     * @var string|null
     */
    protected $invoice_id;
    /**
     * @var Amount|null
     */
    protected $tax_total;

    /**
     * Constructor
     *
     * @param mixed[] $data Associated array of property values initializing the model
     */
    public function __construct(array $data = null)
    {
        $this->invoice_id = isset($data['invoice_id']) ? $data['invoice_id'] : null;
        $this->tax_total = isset($data['tax_total']) ? $data['tax_total'] : null;
    }

    /**
     * Gets invoice_id.
     *
     * @return string|null
     */
    public function getInvoiceId()
    {
        return $this->invoice_id;
    }

    /**
     * Sets invoice_id.
     *
     * @param string|null $invoice_id use this field to pass a purchase identification value of up to 12 ASCII characters for AIB and 17 ASCII characters for all other processors
     *
     * @return $this
     */
    public function setInvoiceId($invoice_id = null)
    {
        $this->invoice_id = $invoice_id;

        return $this;
    }

    /**
     * Gets tax_total.
     *
     * @return Amount|null
     */
    public function getTaxTotal()
    {
        return $this->tax_total;
    }

    /**
     * Sets tax_total.
     *
     * @param Amount|null $tax_total
     *
     * @return $this
     */
    public function setTaxTotal(Amount $tax_total = null)
    {
        $this->tax_total = $tax_total;

        return $this;
    }
}

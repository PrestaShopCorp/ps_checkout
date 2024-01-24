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

class TaxInfo
{
    /**
     * @var string
     */
    private $tax_id;
    /**
     * @var string
     */
    private $tax_id_type;

    /**
     * @return string
     */
    public function getTaxId()
    {
        return $this->tax_id;
    }

    /**
     * @param string $tax_id
     *
     * @return void
     */
    public function setTaxId($tax_id)
    {
        $this->tax_id = $tax_id;
    }

    /**
     * @return string
     */
    public function getTaxIdType()
    {
        return $this->tax_id_type;
    }

    /**
     * @param string $tax_id_type
     *
     * @return void
     */
    public function setTaxIdType($tax_id_type)
    {
        $this->tax_id_type = $tax_id_type;
    }
}

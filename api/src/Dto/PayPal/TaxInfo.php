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
 * The tax ID of the customer. The customer is also known as the payer. Both `tax_id` and `tax_id_type`
 * are required.
 */
class TaxInfo
{
    /**
     * @var string
     */
    private $taxId;

    /**
     * @var string
     */
    private $taxIdType;

    /**
     * @param string $taxId
     * @param string $taxIdType
     */
    public function __construct(string $taxId, string $taxIdType)
    {
        $this->taxId = $taxId;
        $this->taxIdType = $taxIdType;
    }

    /**
     * Returns Tax Id.
     * The customer's tax ID value.
     */
    public function getTaxId(): string
    {
        return $this->taxId;
    }

    /**
     * Sets Tax Id.
     * The customer's tax ID value.
     *
     * @required
     * @maps tax_id
     */
    public function setTaxId(string $taxId): void
    {
        $this->taxId = $taxId;
    }

    /**
     * Returns Tax Id Type.
     * The customer's tax ID type.
     */
    public function getTaxIdType(): string
    {
        return $this->taxIdType;
    }

    /**
     * Sets Tax Id Type.
     * The customer's tax ID type.
     *
     * @required
     * @maps tax_id_type
     */
    public function setTaxIdType(string $taxIdType): void
    {
        $this->taxIdType = $taxIdType;
    }
}

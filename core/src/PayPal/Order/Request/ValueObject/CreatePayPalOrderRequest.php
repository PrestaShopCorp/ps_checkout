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

namespace PsCheckout\Core\PayPal\Order\Request\ValueObject;

class CreatePayPalOrderRequest
{
    /**
     * @var int|null
     */
    private $quantityWanted;

    /**
     * @var int|null
     */
    private $idProduct;

    /**
     * @var int|null
     */
    private $idProductAttribute;

    /**
     * @var int|null
     */
    private $idCustomization;

    /**
     * @var string|null
     */
    private $vaultId;

    /**
     * @var bool
     */
    private $vault;

    /**
     * @var bool
     */
    private $favorite;

    /**
     * @var string
     */
    private $fundingSource;

    /**
     * @var bool
     */
    private $isCardFields;

    /**
     * @var bool
     */
    private $isExpressCheckout;

    /**
     * CheckoutCreateRequest constructor.
     *
     * @param array $request
     */
    public function __construct(array $request)
    {
        $this->quantityWanted = isset($request['quantity_wanted']) ? (int) $request['quantity_wanted'] : null;
        $this->idProduct = isset($request['id_product']) ? (int) $request['id_product'] : null;
        $this->idProductAttribute = isset($request['id_product_attribute']) ? (int) $request['id_product_attribute'] : null;
        $this->idCustomization = isset($request['id_customization']) ? (int) $request['id_customization'] : null;
        $this->vaultId = isset($request['vaultId']) ? (string) $request['vaultId'] : null;
        $this->vault = isset($request['vault']) && (bool) $request['vault'];
        $this->favorite = isset($request['favorite']) && (bool) $request['favorite'];
        $this->fundingSource = isset($request['fundingSource']) ? (string) $request['fundingSource'] : 'paypal';
        $this->isCardFields = isset($request['isCardFields']) && (bool) $request['isCardFields'];
        $this->isExpressCheckout = (bool) $request['isExpressCheckout'];
    }

    /**
     * Get the quantity wanted.
     *
     * @return int|null
     */
    public function getQuantityWanted()
    {
        return $this->quantityWanted;
    }

    /**
     * Get the product ID.
     *
     * @return int|null
     */
    public function getIdProduct()
    {
        return $this->idProduct;
    }

    /**
     * Get the product attribute ID.
     *
     * @return int|null
     */
    public function getIdProductAttribute()
    {
        return $this->idProductAttribute;
    }

    /**
     * Get the customization ID.
     *
     * @return int|null
     */
    public function getIdCustomization()
    {
        return $this->idCustomization;
    }

    /**
     * Get the vault ID.
     *
     * @return string|null
     */
    public function getVaultId()
    {
        return $this->vaultId;
    }

    /**
     * Get whether the vault is enabled.
     *
     * @return bool
     */
    public function isVault(): bool
    {
        return $this->vault;
    }

    /**
     * Get whether this is a favorite item.
     *
     * @return bool
     */
    public function isFavorite(): bool
    {
        return $this->favorite;
    }

    /**
     * Get the funding source.
     *
     * @return string
     */
    public function getFundingSource(): string
    {
        return $this->fundingSource;
    }

    /**
     * Get whether card fields are used.
     *
     * @return bool
     */
    public function isCardFields(): bool
    {
        return $this->isCardFields;
    }

    /**
     * Get whether it is express checkout.
     *
     * @return bool
     */
    public function isExpressCheckout(): bool
    {
        return $this->isExpressCheckout;
    }
}

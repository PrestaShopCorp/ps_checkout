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
 * The Universal Product Code of the item.
 */
class UniversalProductCode
{
    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $code;

    /**
     * @param string $type
     * @param string $code
     */
    public function __construct(string $type, string $code)
    {
        $this->type = $type;
        $this->code = $code;
    }

    /**
     * Returns Type.
     * The Universal Product Code type.
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Sets Type.
     * The Universal Product Code type.
     *
     * @required
     * @maps type
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * Returns Code.
     * The UPC product code of the item.
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * Sets Code.
     * The UPC product code of the item.
     *
     * @required
     * @maps code
     */
    public function setCode(string $code): void
    {
        $this->code = $code;
    }
}

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

namespace PsCheckout\Core\Order\Builder\Node;

interface BaseNodeBuilderInterface
{
    /**
     * @return array
     */
    public function build(): array;

    /**
     * @param array $cart
     *
     * @return self
     */
    public function setCart(array $cart): BaseNodeBuilder;

    /**
     * @param bool $isVault
     *
     * @return self
     */
    public function setIsVault(bool $isVault): BaseNodeBuilder;

    /**
     * @param bool $isUpdate
     *
     * @return self
     */
    public function setIsUpdate(bool $isUpdate): BaseNodeBuilder;

    /**
     * @param string|null $paypalOrderId
     *
     * @return self
     */
    public function setPaypalOrderId($paypalOrderId): BaseNodeBuilder;
}

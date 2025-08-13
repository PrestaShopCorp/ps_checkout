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

namespace PsCheckout\Infrastructure\Adapter;

interface ShopContextInterface
{
    /**
     * Retrieves the current context.
     *
     * @return array{
     *     context: int|null,
     *     shop_id: int|null,
     *     group_shop_id: int|null
     * } Current shop context information
     */
    public function getCurrent(): array;

    /**
     * Sets the specified shop context.
     *
     * @return void
     */
    public function setAllShopContext();

    /**
     * Sets the specified shop context.
     *
     * @param array{
     *     context: int|string|null,
     *     shop_id?: int|null,
     *     group_shop_id?: int|null
     * } $context Context information to apply
     *
     * @return void
     */
    public function setContext(array $context);
}

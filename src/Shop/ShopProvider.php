<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */

namespace PrestaShop\Module\PrestashopCheckout\Shop;

use Context;
use PrestaShop\Module\PrestashopCheckout\Exception\PsCheckoutException;
use Shop;

/**
 * Class responsible to provide current PrestaShop Shop data
 */
class ShopProvider
{
    /**
     * @return int
     *
     * @throws PsCheckoutException
     */
    public function getIdentifier()
    {
        if (Context::getContext()->shop instanceof Shop) {
            return (int) Context::getContext()->shop->id;
        }

        throw new PsCheckoutException('Unable to retrieve current shop identifier.');
    }

    /**
     * @return int
     *
     * @throws PsCheckoutException
     */
    public function getGroupIdentifier()
    {
        if (Context::getContext()->shop instanceof Shop) {
            return (int) Context::getContext()->shop->id_shop_group;
        }

        throw new PsCheckoutException('Unable to retrieve current shop group identifier.');
    }
}

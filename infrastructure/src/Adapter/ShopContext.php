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

class ShopContext implements ShopContextInterface
{
    /**
     * {@inheritdoc}
     */
    public function getCurrent(): array
    {
        return [
            'context' => \Shop::getContext(),
            'shop_id' => \Shop::getContextShopID(),
            'group_shop_id' => \Shop::getContextShopGroupID(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function setAllShopContext()
    {
        \Shop::setContext(\Shop::CONTEXT_ALL);
    }

    /**
     * {@inheritdoc}
     */
    public function setContext(array $context)
    {
        if (isset($context['shop_id'])) {
            \Shop::setContext($context['context'], $context['shop_id']);
        } elseif (isset($context['group_shop_id'])) {
            \Shop::setContext($context['context'], $context['group_shop_id']);
        } else {
            \Shop::setContext($context['context']);
        }
    }
}

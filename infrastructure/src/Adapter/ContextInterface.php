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

use PrestaShopBundle\Bridge\AdminController\LegacyControllerBridgeInterface;
use PrestaShopBundle\Install\Language as InstallLanguage;

interface ContextInterface
{
    /**
     * @return \Customer|null
     */
    public function getCustomer();

    /**
     * @return \Cart|null
     */
    public function getCart();

    /**
     * @return \Country|null
     */
    public function getCountry();

    /**
     * @return \Language|InstallLanguage|null
     */
    public function getLanguage();

    /**
     * @return \Currency|null
     */
    public function getCurrency();

    /**
     * @return \Link|null
     */
    public function getLink();

    /**
     * @return \AdminController|\FrontController|LegacyControllerBridgeInterface|null
     */
    public function getController();

    /**
     * @return string
     */
    public function getCurrentThemeName(): string;

    /**
     * Get the currency ISO code (ISO 4217) from the context
     *
     * @return string
     */
    public function getCurrencyIsoCode(): string;

    /**
     * @return \Shop|null
     */
    public function getShop();

    /**
     * @param \Cart $cart
     *
     * @return void
     */
    public function setCurrentCart(\Cart $cart);

    /**
     * @param \Customer $customer
     *
     * @return void
     */
    public function updateCustomer(\Customer $customer);

    /**
     * @return void
     */
    public function resetContextCartAddresses();
}

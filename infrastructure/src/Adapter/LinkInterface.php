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

interface LinkInterface
{
    /**
     * Adapter for getAdminLink from prestashop link class
     *
     * @param string $controller controller name
     * @param bool $withToken include or not the token in the url
     * @param array $sfRouteParams
     * @param array $params
     *
     * @return string
     *
     * @throws \PrestaShopException
     */
    public function getAdminLink(string $controller, bool $withToken = true, array $sfRouteParams = [], array $params = []): string;

    /**
     * @param string $controller
     * @param array $params
     *
     * @return string
     */
    public function getModuleLink(string $controller, array $params = []): string;

    /**
     * @param string $controller
     * @param array $params
     *
     * @return string
     */
    public function getPageLink(string $controller, array $params = []): string;
}

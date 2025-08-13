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

namespace PsCheckout\Infrastructure\Environment;

interface EnvInterface
{
    /**
     * Get environment variable.
     *
     * @param string $name
     *
     * @return mixed
     */
    public function getEnv(string $name);

    /**
     * @return string
     */
    public function getCheckoutApiUrl(): string;

    /**
     * @return string
     */
    public function getShipmentTrackingApiUrl(): string;

    /**
     * Retrieve payment api url
     *
     * @return string
     */
    public function getPaymentApiUrl(): string;

    /**
     * @return string
     */
    public function getPaypalClientId(): string;

    /**
     * Retrieve the bn code
     *
     * @return string
     */
    public function getBnCode(): string;
}

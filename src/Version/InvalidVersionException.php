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

namespace PrestaShop\Module\PrestashopCheckout\Version;

use PrestaShop\Module\PrestashopCheckout\Exception\PsCheckoutException;

class InvalidVersionException extends PsCheckoutException
{
    /**
     * @param string $version
     */
    public function __construct($version)
    {
        $message = sprintf(
            'You provided an invalid version string ("%s"). A valid version string ' .
            'must contain numeric characters separated by "." characters, for example "6.3.1.0".',
            $version
        );
        parent::__construct($message, 0, null);
    }
}

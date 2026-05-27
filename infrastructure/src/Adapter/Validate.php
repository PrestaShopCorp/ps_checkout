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

use Validate as PrestaShopValidate;

class Validate implements ValidateInterface
{
    /**
     * Canonical email pattern from the PayPal Orders v2 API spec (docs/checkout_orders_v2.json).
     * Stricter than PrestaShop's own isEmail(): requires a dot-separated domain (TLD mandatory).
     */
    const PAYPAL_EMAIL_PATTERN = '/^.*(?:[a-zA-Z0-9!#$%&\'*+\/=?^_`{|}~-]+(?:\.[a-zA-Z0-9!#$%&\'*+\/=?^_`{|}~-]+)*'
        . '|(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21\x23-\x5b\x5d-\x7f]|\\\\[\x01-\x09\x0b\x0c\x0e-\x7f])*")'
        . '@(?:(?:[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?\.)+[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?'
        . '|\\[(?:(?:(2(5[0-5]|[0-4][0-9])|1[0-9][0-9]|[1-9]?[0-9]))\.){3}'
        . '(?:(2(5[0-5]|[0-4][0-9])|1[0-9][0-9]|[1-9]?[0-9])|[a-zA-Z0-9-]*[a-zA-Z0-9]:'
        . '(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21-\x5a\x53-\x7f]|\\\\[\x01-\x09\x0b\x0c\x0e-\x7f])+)\\]).*$/S';

    /**
     * {@inheritDoc}
     */
    public function isEmail(string $email): bool
    {
        return PrestaShopValidate::isEmail($email);
    }

    /**
     * {@inheritDoc}
     */
    public function isGenericName(string $name): bool
    {
        return PrestaShopValidate::isGenericName($name);
    }

    /**
     * {@inheritDoc}
     */
    public function isFileName(string $filename): bool
    {
        return PrestaShopValidate::isFileName($filename);
    }

    /**
     * {@inheritDoc}
     */
    public function isPayPalEmail(string $email): bool
    {
        return (bool) preg_match(self::PAYPAL_EMAIL_PATTERN, $email);
    }
}

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

namespace PrestaShop\Module\PrestashopCheckout\PayPal\Customer\ValueObject;

use InvalidArgumentException;

class PayPalCustomerId
{
    /**
     * @var string
     */
    private $customerId;

    /**
     * @param string $customerId
     *
     * @throws InvalidArgumentException
     */
    public function __construct($customerId)
    {
        $this->assertIsValid($customerId);
        $this->customerId = $customerId;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->customerId;
    }

    /**
     * @param string $customerId
     *
     * @return void
     *
     * @throws InvalidArgumentException
     */
    private function assertIsValid($customerId)
    {
        if (!is_string($customerId)) {
            throw new InvalidArgumentException('PayPal Customer ID must be a string.');
        }

        $length = strlen($customerId);

        if ($length < 1 || $length > 22) {
            throw new InvalidArgumentException('PayPal Customer ID must be between 1 and 22 characters long.');
        }

        if (preg_match('/^[0-9a-zA-Z_-]+$/', $customerId) !== 1) {
            throw new InvalidArgumentException('PayPal Customer ID must be alphanumeric.');
        }
    }
}

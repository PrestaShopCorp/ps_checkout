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

namespace PrestaShop\Module\PrestashopCheckout\PayPal\PaymentToken\ValueObject;

use InvalidArgumentException;

class PaymentTokenId
{
    /**
     * @var string
     */
    private $id;

    /**
     * @param string $id
     *
     * @throws InvalidArgumentException
     */
    public function __construct($id)
    {
        $this->assertIsValid($id);
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->id;
    }

    /**
     * @param string $id
     *
     * @return void
     *
     * @throws InvalidArgumentException
     */
    private function assertIsValid($id)
    {
        if (!is_string($id)) {
            throw new InvalidArgumentException('PayPal Vault ID must be a string.');
        }

        $length = strlen($id);

        if ($length < 1 || $length > 36) {
            throw new InvalidArgumentException('PayPal Vault ID must be between 1 and 36 characters long.');
        }

        if (preg_match('/^[0-9a-zA-Z_-]+$/', $id) !== 1) {
            throw new InvalidArgumentException('PayPal Vault ID must be alphanumeric.');
        }
    }
}

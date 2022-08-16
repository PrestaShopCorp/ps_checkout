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

namespace PrestaShop\Module\PrestashopCheckout\Order\Command;

use PrestaShop\Module\PrestashopCheckout\Exception\InvalidModuleException;
use PrestaShop\Module\PrestashopCheckout\Exception\InvalidOrderStateException;
use PrestaShop\Module\PrestashopCheckout\Order\CheckoutCartId;

class CreateOrderCommand
{
    /**
     * @var CheckoutCartId
     */
    private $cartId;

    /**
     * @var string
     */
    private $paymentModuleName;

    /**
     * @var int
     */
    private $orderStateId;

    /**
     * @param int $cartId
     * @param string $paymentModuleName
     * @param int $orderStateId
     */
    public function __construct($cartId, $paymentModuleName, $orderStateId)
    {
        $this->cartId = new CheckoutCartId($cartId);
        $this->assertIsModuleName($paymentModuleName);
        $this->assertOrderStateIsPositiveInt($orderStateId);

        $this->paymentModuleName = $paymentModuleName;
        $this->orderStateId = $orderStateId;
    }

    /**
     * @return CheckoutCartId
     */
    public function getCartId()
    {
        return $this->cartId;
    }

    /**
     * @return string
     */
    public function getPaymentModuleName()
    {
        return $this->paymentModuleName;
    }

    /**
     * @return int
     */
    public function getOrderStateId()
    {
        return $this->orderStateId;
    }

    /**
     * @param string $moduleName
     *
     * @throws InvalidModuleException
     */
    private function assertIsModuleName($moduleName)
    {
        if (!is_string($moduleName) || !preg_match('/^[a-zA-Z0-9_-]+$/', $moduleName)) {
            throw new InvalidModuleException(sprintf('Invalid PaymentModule name, got %s', var_export($moduleName, true)));
        }
    }

    /**
     * @param int $orderStateId
     *
     * @throws InvalidOrderStateException
     */
    private function assertOrderStateIsPositiveInt($orderStateId)
    {
        if (!is_int($orderStateId) || 0 >= $orderStateId) {
            throw new InvalidOrderStateException(InvalidOrderStateException::INVALID_ID, 'Invalid order state id');
        }
    }
}

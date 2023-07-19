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

namespace PrestaShop\Module\PrestashopCheckout\Order\State\Service;

use PrestaShop\Module\PrestashopCheckout\Configuration\PrestaShopConfiguration;
use PrestaShop\Module\PrestashopCheckout\Order\State\Exception\OrderStateException;
use PrestaShop\Module\PrestashopCheckout\Order\State\OrderStateConfigurationKeys;

class OrderStateMapper
{
    /**
     * @var PrestaShopConfiguration
     */
    private $configuration;

    /**
     * @var array
     */
    private $orderStateMapping;

    /**
     * @param PrestaShopConfiguration $configuration
     */
    public function __construct(PrestaShopConfiguration $configuration)
    {
        $this->configuration = $configuration;
        $this->initialize();
    }

    /**
     * @param string $key
     *
     * @return int
     *
     * @throws OrderStateException
     */
    public function getIdByKey($key)
    {
        if (isset($this->orderStateMapping[$key]) && $this->orderStateMapping[$key]) {
            return $this->orderStateMapping[$key];
        }

        throw new OrderStateException(sprintf('Order state key "%s" is not mapped', var_export($key, true)), OrderStateException::INVALID_MAPPING);
    }

    /**
     * @return array
     *
     * @throws OrderStateException
     */
    public function getMappedOrderStates()
    {
        return [
            OrderStateConfigurationKeys::PS_CHECKOUT_STATE_PENDING => [
                'default' => '0',
                'value' => (string) $this->getIdByKey(OrderStateConfigurationKeys::PS_CHECKOUT_STATE_PENDING),
            ],
            OrderStateConfigurationKeys::PS_CHECKOUT_STATE_COMPLETED => [
                'default' => (string) $this->getIdByKey(OrderStateConfigurationKeys::PS_OS_PAYMENT),
                'value' => (string) $this->getIdByKey(OrderStateConfigurationKeys::PS_CHECKOUT_STATE_COMPLETED),
            ],
            OrderStateConfigurationKeys::PS_CHECKOUT_STATE_CANCELED => [
                'default' => (string) $this->getIdByKey(OrderStateConfigurationKeys::PS_OS_CANCELED),
                'value' => (string) $this->getIdByKey(OrderStateConfigurationKeys::PS_CHECKOUT_STATE_CANCELED),
            ],
            OrderStateConfigurationKeys::PS_CHECKOUT_STATE_ERROR => [
                'default' => (string) $this->getIdByKey(OrderStateConfigurationKeys::PS_OS_ERROR),
                'value' => (string) $this->getIdByKey(OrderStateConfigurationKeys::PS_CHECKOUT_STATE_ERROR),
            ],
            OrderStateConfigurationKeys::PS_CHECKOUT_STATE_REFUNDED => [
                'default' => (string) $this->getIdByKey(OrderStateConfigurationKeys::PS_OS_REFUND),
                'value' => (string) $this->getIdByKey(OrderStateConfigurationKeys::PS_CHECKOUT_STATE_REFUNDED),
            ],
            OrderStateConfigurationKeys::PS_CHECKOUT_STATE_PARTIALLY_REFUNDED => [
                'default' => '0',
                'value' => (string) $this->getIdByKey(OrderStateConfigurationKeys::PS_CHECKOUT_STATE_PARTIALLY_REFUNDED),
            ],
            OrderStateConfigurationKeys::PS_CHECKOUT_STATE_PARTIALLY_PAID => [
                'default' => '0',
                'value' => (string) $this->getIdByKey(OrderStateConfigurationKeys::PS_CHECKOUT_STATE_PARTIALLY_PAID),
            ],
            OrderStateConfigurationKeys::PS_CHECKOUT_STATE_AUTHORIZED => [
                'default' => '0',
                'value' => (string) $this->getIdByKey(OrderStateConfigurationKeys::PS_CHECKOUT_STATE_AUTHORIZED),
            ],
        ];
    }

    /**
     * @param int $orderCurrentState
     *
     * @return string
     *
     * @throws OrderStateException
     */
    public function getKeyById($orderCurrentState)
    {
        $orderStateMapping = array_flip($this->orderStateMapping);

        if (isset($orderStateMapping[$orderCurrentState]) && $orderStateMapping[$orderCurrentState]) {
            return $orderStateMapping[$orderCurrentState];
        }

        throw new OrderStateException(sprintf('Order state id "%s" is not mapped', var_export($orderCurrentState, true)), OrderStateException::INVALID_MAPPING);
    }

    private function initialize()
    {
        $this->orderStateMapping = [
            // PrestaShop native order statuses
            OrderStateConfigurationKeys::PS_OS_CANCELED => (int) $this->configuration->get(OrderStateConfigurationKeys::PS_OS_CANCELED, ['global' => true]),
            OrderStateConfigurationKeys::PS_OS_ERROR => (int) $this->configuration->get(OrderStateConfigurationKeys::PS_OS_ERROR, ['global' => true]),
            OrderStateConfigurationKeys::PS_OS_OUTOFSTOCK_UNPAID => (int) $this->configuration->get(OrderStateConfigurationKeys::PS_OS_OUTOFSTOCK_UNPAID, ['global' => true]),
            OrderStateConfigurationKeys::PS_OS_OUTOFSTOCK_PAID => (int) $this->configuration->get(OrderStateConfigurationKeys::PS_OS_OUTOFSTOCK_PAID, ['global' => true]),
            OrderStateConfigurationKeys::PS_OS_PAYMENT => (int) $this->configuration->get(OrderStateConfigurationKeys::PS_OS_PAYMENT, ['global' => true]),
            OrderStateConfigurationKeys::PS_OS_REFUND => (int) $this->configuration->get(OrderStateConfigurationKeys::PS_OS_REFUND, ['global' => true]),
            // PrestaShop Checkout order statuses
            OrderStateConfigurationKeys::PS_CHECKOUT_STATE_PENDING => (int) $this->configuration->get(OrderStateConfigurationKeys::PS_CHECKOUT_STATE_PENDING, ['global' => true]),
            OrderStateConfigurationKeys::PS_CHECKOUT_STATE_COMPLETED => (int) $this->configuration->get(OrderStateConfigurationKeys::PS_CHECKOUT_STATE_COMPLETED, ['global' => true]),
            OrderStateConfigurationKeys::PS_CHECKOUT_STATE_CANCELED => (int) $this->configuration->get(OrderStateConfigurationKeys::PS_CHECKOUT_STATE_CANCELED, ['global' => true]),
            OrderStateConfigurationKeys::PS_CHECKOUT_STATE_ERROR => (int) $this->configuration->get(OrderStateConfigurationKeys::PS_CHECKOUT_STATE_ERROR, ['global' => true]),
            OrderStateConfigurationKeys::PS_CHECKOUT_STATE_REFUNDED => (int) $this->configuration->get(OrderStateConfigurationKeys::PS_CHECKOUT_STATE_REFUNDED, ['global' => true]),
            OrderStateConfigurationKeys::PS_CHECKOUT_STATE_PARTIALLY_REFUNDED => (int) $this->configuration->get(OrderStateConfigurationKeys::PS_CHECKOUT_STATE_PARTIALLY_REFUNDED, ['global' => true]),
            OrderStateConfigurationKeys::PS_CHECKOUT_STATE_PARTIALLY_PAID => (int) $this->configuration->get(OrderStateConfigurationKeys::PS_CHECKOUT_STATE_PARTIALLY_PAID, ['global' => true]),
            OrderStateConfigurationKeys::PS_CHECKOUT_STATE_AUTHORIZED => (int) $this->configuration->get(OrderStateConfigurationKeys::PS_CHECKOUT_STATE_AUTHORIZED, ['global' => true]),
            // PrestaShop Checkout deprecated order statuses
            OrderStateConfigurationKeys::PS_CHECKOUT_STATE_PARTIAL_REFUND => (int) $this->configuration->get(OrderStateConfigurationKeys::PS_CHECKOUT_STATE_PARTIAL_REFUND, ['global' => true]),
            OrderStateConfigurationKeys::PS_CHECKOUT_STATE_WAITING_CAPTURE => (int) $this->configuration->get(OrderStateConfigurationKeys::PS_CHECKOUT_STATE_WAITING_CAPTURE, ['global' => true]),
            OrderStateConfigurationKeys::PS_CHECKOUT_STATE_WAITING_CREDIT_CARD_PAYMENT => (int) $this->configuration->get(OrderStateConfigurationKeys::PS_CHECKOUT_STATE_WAITING_CREDIT_CARD_PAYMENT, ['global' => true]),
            OrderStateConfigurationKeys::PS_CHECKOUT_STATE_WAITING_LOCAL_PAYMENT => (int) $this->configuration->get(OrderStateConfigurationKeys::PS_CHECKOUT_STATE_WAITING_LOCAL_PAYMENT, ['global' => true]),
            OrderStateConfigurationKeys::PS_CHECKOUT_STATE_WAITING_PAYPAL_PAYMENT => (int) $this->configuration->get(OrderStateConfigurationKeys::PS_CHECKOUT_STATE_WAITING_PAYPAL_PAYMENT, ['global' => true]),
        ];
    }
}

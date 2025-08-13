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

namespace PsCheckout\Core\OrderState\Service;

use PsCheckout\Core\OrderState\Configuration\OrderStateConfiguration;
use PsCheckout\Core\OrderState\OrderStateException;
use PsCheckout\Infrastructure\Adapter\ConfigurationInterface;

class OrderStateMapper implements OrderStateMapperInterface
{
    /**
     * @var ConfigurationInterface
     */
    private $configuration;

    /**
     * @var array
     */
    private $orderStateMapping;

    /**
     * @param ConfigurationInterface $configuration
     */
    public function __construct(ConfigurationInterface $configuration)
    {
        $this->configuration = $configuration;
        $this->initialize();
    }

    /**
     * {@inheritdoc}
     */
    public function getIdByKey(string $key): int
    {
        if (isset($this->orderStateMapping[$key]) && $this->orderStateMapping[$key]) {
            return $this->orderStateMapping[$key];
        }

        throw new OrderStateException(sprintf('Order state key "%s" is not mapped', var_export($key, true)), OrderStateException::INVALID_MAPPING);
    }

    /**
     * {@inheritdoc}
     */
    public function getMappedOrderStates(): array
    {
        return [
            OrderStateConfiguration::PS_CHECKOUT_STATE_PENDING => [
                'default' => '0',
                'value' => (string) $this->getIdByKey(OrderStateConfiguration::PS_CHECKOUT_STATE_PENDING),
            ],
            OrderStateConfiguration::PS_CHECKOUT_STATE_COMPLETED => [
                'default' => (string) $this->getIdByKey(OrderStateConfiguration::PS_OS_PAYMENT),
                'value' => (string) $this->getIdByKey(OrderStateConfiguration::PS_CHECKOUT_STATE_COMPLETED),
            ],
            OrderStateConfiguration::PS_CHECKOUT_STATE_CANCELED => [
                'default' => (string) $this->getIdByKey(OrderStateConfiguration::PS_OS_CANCELED),
                'value' => (string) $this->getIdByKey(OrderStateConfiguration::PS_CHECKOUT_STATE_CANCELED),
            ],
            OrderStateConfiguration::PS_CHECKOUT_STATE_ERROR => [
                'default' => (string) $this->getIdByKey(OrderStateConfiguration::PS_OS_ERROR),
                'value' => (string) $this->getIdByKey(OrderStateConfiguration::PS_CHECKOUT_STATE_ERROR),
            ],
            OrderStateConfiguration::PS_CHECKOUT_STATE_REFUNDED => [
                'default' => (string) $this->getIdByKey(OrderStateConfiguration::PS_OS_REFUND),
                'value' => (string) $this->getIdByKey(OrderStateConfiguration::PS_CHECKOUT_STATE_REFUNDED),
            ],
            OrderStateConfiguration::PS_CHECKOUT_STATE_PARTIALLY_REFUNDED => [
                'default' => '0',
                'value' => (string) $this->getIdByKey(OrderStateConfiguration::PS_CHECKOUT_STATE_PARTIALLY_REFUNDED),
            ],
            OrderStateConfiguration::PS_CHECKOUT_STATE_PARTIALLY_PAID => [
                'default' => '0',
                'value' => (string) $this->getIdByKey(OrderStateConfiguration::PS_CHECKOUT_STATE_PARTIALLY_PAID),
            ],
            OrderStateConfiguration::PS_CHECKOUT_STATE_AUTHORIZED => [
                'default' => '0',
                'value' => (string) $this->getIdByKey(OrderStateConfiguration::PS_CHECKOUT_STATE_AUTHORIZED),
            ],
        ];
    }

    private function initialize()
    {
        $this->orderStateMapping = [
            // PrestaShop native order statuses
            OrderStateConfiguration::PS_OS_CANCELED => $this->configuration->getInteger(OrderStateConfiguration::PS_OS_CANCELED),
            OrderStateConfiguration::PS_OS_ERROR => $this->configuration->getInteger(OrderStateConfiguration::PS_OS_ERROR),
            OrderStateConfiguration::PS_OS_OUTOFSTOCK_UNPAID => $this->configuration->getInteger(OrderStateConfiguration::PS_OS_OUTOFSTOCK_UNPAID),
            OrderStateConfiguration::PS_OS_OUTOFSTOCK_PAID => $this->configuration->getInteger(OrderStateConfiguration::PS_OS_OUTOFSTOCK_PAID),
            OrderStateConfiguration::PS_OS_PAYMENT => $this->configuration->getInteger(OrderStateConfiguration::PS_OS_PAYMENT),
            OrderStateConfiguration::PS_OS_REFUND => $this->configuration->getInteger(OrderStateConfiguration::PS_OS_REFUND),
            // PrestaShop Checkout order statuses
            OrderStateConfiguration::PS_CHECKOUT_STATE_PENDING => $this->configuration->getInteger(OrderStateConfiguration::PS_CHECKOUT_STATE_PENDING),
            OrderStateConfiguration::PS_CHECKOUT_STATE_COMPLETED => $this->configuration->getInteger(OrderStateConfiguration::PS_CHECKOUT_STATE_COMPLETED),
            OrderStateConfiguration::PS_CHECKOUT_STATE_CANCELED => $this->configuration->getInteger(OrderStateConfiguration::PS_CHECKOUT_STATE_CANCELED),
            OrderStateConfiguration::PS_CHECKOUT_STATE_ERROR => $this->configuration->getInteger(OrderStateConfiguration::PS_CHECKOUT_STATE_ERROR),
            OrderStateConfiguration::PS_CHECKOUT_STATE_REFUNDED => $this->configuration->getInteger(OrderStateConfiguration::PS_CHECKOUT_STATE_REFUNDED),
            OrderStateConfiguration::PS_CHECKOUT_STATE_PARTIALLY_REFUNDED => $this->configuration->getInteger(OrderStateConfiguration::PS_CHECKOUT_STATE_PARTIALLY_REFUNDED),
            OrderStateConfiguration::PS_CHECKOUT_STATE_PARTIALLY_PAID => $this->configuration->getInteger(OrderStateConfiguration::PS_CHECKOUT_STATE_PARTIALLY_PAID),
            OrderStateConfiguration::PS_CHECKOUT_STATE_AUTHORIZED => $this->configuration->getInteger(OrderStateConfiguration::PS_CHECKOUT_STATE_AUTHORIZED),
        ];
    }
}

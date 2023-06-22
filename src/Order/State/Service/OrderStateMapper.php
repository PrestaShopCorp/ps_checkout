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
        $this->orderStateMapping = [
            OrderStateConfigurationKeys::CANCELED => (int) $this->configuration->get(OrderStateConfigurationKeys::CANCELED, ['global' => true]),
            OrderStateConfigurationKeys::PAYMENT_ERROR => (int) $this->configuration->get(OrderStateConfigurationKeys::PAYMENT_ERROR, ['global' => true]),
            OrderStateConfigurationKeys::OUT_OF_STOCK_UNPAID => (int) $this->configuration->get(OrderStateConfigurationKeys::OUT_OF_STOCK_UNPAID, ['global' => true]),
            OrderStateConfigurationKeys::OUT_OF_STOCK_PAID => (int) $this->configuration->get(OrderStateConfigurationKeys::OUT_OF_STOCK_PAID, ['global' => true]),
            OrderStateConfigurationKeys::PAYMENT_ACCEPTED => (int) $this->configuration->get(OrderStateConfigurationKeys::PAYMENT_ACCEPTED, ['global' => true]),  /* @phpstan-ignore-line */
            OrderStateConfigurationKeys::REFUNDED => (int) $this->configuration->get(OrderStateConfigurationKeys::REFUNDED, ['global' => true]),
            OrderStateConfigurationKeys::AUTHORIZED => (int) $this->configuration->get(OrderStateConfigurationKeys::AUTHORIZED, ['global' => true]),
            OrderStateConfigurationKeys::PARTIALLY_PAID => (int) $this->configuration->get(OrderStateConfigurationKeys::PARTIALLY_PAID, ['global' => true]), /* @phpstan-ignore-line */
            OrderStateConfigurationKeys::PARTIALLY_REFUNDED => (int) $this->configuration->get(OrderStateConfigurationKeys::PARTIALLY_REFUNDED, ['global' => true]),
            OrderStateConfigurationKeys::WAITING_CAPTURE => (int) $this->configuration->get(OrderStateConfigurationKeys::WAITING_CAPTURE, ['global' => true]),
            OrderStateConfigurationKeys::WAITING_CREDIT_CARD_PAYMENT => (int) $this->configuration->get(OrderStateConfigurationKeys::WAITING_CREDIT_CARD_PAYMENT, ['global' => true]),
            OrderStateConfigurationKeys::WAITING_PAYPAL_PAYMENT => (int) $this->configuration->get(OrderStateConfigurationKeys::WAITING_PAYPAL_PAYMENT, ['global' => true]),
            OrderStateConfigurationKeys::WAITING_LOCAL_PAYMENT => (int) $this->configuration->get(OrderStateConfigurationKeys::WAITING_LOCAL_PAYMENT, ['global' => true]),
        ];
    }

    /**
     * @param string $key
     *
     * @return int
     */
    public function getIdByKey($key)
    {
        if (isset($this->orderStateMapping[$key]) && $this->orderStateMapping[$key]) {
            return $this->orderStateMapping[$key];
        }

        throw new \InvalidArgumentException(sprintf('Order state key "%s" is not mapped', var_export($key, true)));
    }

    /**
     * @return array
     */
    public function getOrderStateMapping()
    {
        return $this->orderStateMapping;
    }

    /**
     * @param int $orderCurrentState
     *
     * @return string
     */
    public function getKeyById($orderCurrentState)
    {
        $orderStateMapping = array_flip($this->orderStateMapping);

        if (isset($orderStateMapping[$orderCurrentState]) && $orderStateMapping[$orderCurrentState]) {
            return $orderStateMapping[$orderCurrentState];
        }

        throw new \InvalidArgumentException(sprintf('Order state id "%s" is not mapped', var_export($orderCurrentState, true)));
    }
}

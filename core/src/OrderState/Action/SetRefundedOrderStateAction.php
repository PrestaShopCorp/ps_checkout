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

namespace PsCheckout\Core\OrderState\Action;

use PsCheckout\Core\Order\Exception\OrderException;
use PsCheckout\Core\OrderState\Configuration\OrderStateConfiguration;
use PsCheckout\Core\OrderState\Service\OrderStateMapperInterface;
use PsCheckout\Core\PayPal\Order\Cache\PayPalOrderCacheInterface;
use PsCheckout\Core\PayPal\Order\Provider\PayPalOrderProviderInterface;
use PsCheckout\Core\PayPal\Refund\Provider\PayPalRefundOrderProviderInterface;
use PsCheckout\Core\PayPal\Refund\ValueObject\PayPalRefundOrder;

class SetRefundedOrderStateAction implements SetOrderStateActionInterface
{
    /**
     * @var PayPalRefundOrderProviderInterface
     */
    private $payPalRefundOrderProvider;

    /**
     * @var PayPalOrderProviderInterface
     */
    private $payPalOrderProvider;

    /**
     * @var OrderStateMapperInterface
     */
    private $orderStateMapper;

    /**
     * @var ChangeOrderStateActionInterface
     */
    private $changeOrderStateAction;

    /**
     * @var PayPalOrderCacheInterface
     */
    private $orderPayPalCache;

    /**
     * @param PayPalRefundOrderProviderInterface $payPalRefundOrderProvider
     * @param PayPalOrderProviderInterface $payPalOrderProvider
     * @param OrderStateMapperInterface $orderStateMapper
     * @param ChangeOrderStateActionInterface $changeOrderStateAction
     * @param PayPalOrderCacheInterface $orderPayPalCache
     */
    public function __construct(
        PayPalRefundOrderProviderInterface $payPalRefundOrderProvider,
        PayPalOrderProviderInterface $payPalOrderProvider,
        OrderStateMapperInterface $orderStateMapper,
        ChangeOrderStateActionInterface $changeOrderStateAction,
        PayPalOrderCacheInterface $orderPayPalCache
    ) {
        $this->payPalRefundOrderProvider = $payPalRefundOrderProvider;
        $this->payPalOrderProvider = $payPalOrderProvider;
        $this->orderStateMapper = $orderStateMapper;
        $this->changeOrderStateAction = $changeOrderStateAction;
        $this->orderPayPalCache = $orderPayPalCache;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(string $payPalOrderId)
    {
        try {
            /** @var PayPalRefundOrder $refundOrder */
            $refundOrder = $this->payPalRefundOrderProvider->provide($payPalOrderId);
        } catch (OrderException $exception) {
            return;
        }

        if (!$refundOrder->hasBeenPaid() || $refundOrder->hasBeenTotallyRefund()) {
            return;
        }

        if ($this->orderPayPalCache->has($payPalOrderId)) {
            $this->orderPayPalCache->delete($payPalOrderId);
        }

        $payPalOrderResponse = $this->payPalOrderProvider->getById($payPalOrderId);

        if (!$payPalOrderResponse || empty($payPalOrderResponse->getRefunds())) {
            return;
        }

        $totalRefunded = array_reduce($payPalOrderResponse->getRefunds(), function ($totalRefunded, $refund) {
            return $totalRefunded + (float) $refund['amount']['value'];
        });

        $orderFullyRefunded = (float) $refundOrder->getTotalAmount() <= round($totalRefunded, 2);
        $orderStateRefunded = $this->orderStateMapper->getIdByKey(OrderStateConfiguration::PS_CHECKOUT_STATE_REFUNDED);
        $orderStatePartiallyRefunded = $this->orderStateMapper->getIdByKey(OrderStateConfiguration::PS_CHECKOUT_STATE_PARTIALLY_REFUNDED);
        $newOrderState = $orderFullyRefunded ? $orderStateRefunded : $orderStatePartiallyRefunded;

        if ($refundOrder->hasBeenPartiallyRefund() && $newOrderState === $orderStatePartiallyRefunded) {
            return;
        }

        if ($refundOrder->getCurrentStateId() === $newOrderState) {
            return;
        }

        $this->changeOrderStateAction->execute($refundOrder->getOrderId(), $newOrderState);
    }
}

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

namespace PrestaShop\Module\PrestashopCheckout\PayPal\Order;

use PrestaShop\Module\PrestashopCheckout\CommandBus\CommandBusInterface;
use PrestaShop\Module\PrestashopCheckout\Event\EventDispatcherInterface;
use PrestaShop\Module\PrestashopCheckout\Order\Exception\OrderException;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Comparator\PayPalOrderComparator;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Event\PayPalOrderApprovedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Event\PayPalOrderCompletedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Event\PayPalOrderCreatedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Event\PayPalOrderSavedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Query\GetCurrentPayPalOrderStatusQuery;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Query\GetCurrentPayPalOrderStatusQueryResult;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;

class PayPalOrderEventDispatcher
{
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var CommandBusInterface
     */
    private $commandBus;

    /**
     * @var CheckTransitionPayPalOrderStatusService
     */
    private $checkTransitionPayPalOrderStatusService;

    /**
     * @var PayPalOrderComparator
     */
    private $paypalOrderComparator;

    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @param EventDispatcherInterface $eventDispatcher
     * @param CommandBusInterface $commandBus
     * @param CheckTransitionPayPalOrderStatusService $checkTransitionPayPalOrderStatusService
     * @param PayPalOrderComparator $paypalOrderComparator
     * @param CacheInterface $cache
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        CommandBusInterface $commandBus,
        CheckTransitionPayPalOrderStatusService $checkTransitionPayPalOrderStatusService,
        PayPalOrderComparator $paypalOrderComparator,
        CacheInterface $cache
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->commandBus = $commandBus;
        $this->checkTransitionPayPalOrderStatusService = $checkTransitionPayPalOrderStatusService;
        $this->paypalOrderComparator = $paypalOrderComparator;
        $this->cache = $cache;
    }

    /**
     * @param array $orderPayPal
     *
     * @return void
     *
     * @throws Exception\PayPalOrderException
     * @throws OrderException
     * @throws InvalidArgumentException
     */
    public function dispatch(array $orderPayPal)
    {
        $module = \Module::getInstanceByName('ps_checkout');
        $module->getLogger()->debug(
            __CLASS__ . ' - ' . __FUNCTION__,
            [
                'PayPalOrderId' => $orderPayPal['id'],
            ]
        );

        $cacheOrderPayPal = $this->cache->get($orderPayPal['id']);
        if (empty($cacheOrderPayPal)) {
            /** @var GetCurrentPayPalOrderStatusQueryResult $getCurrentPayPalOrderStatusQueryResult */
            $getCurrentPayPalOrderStatusQueryResult = $this->commandBus->handle(new GetCurrentPayPalOrderStatusQuery($orderPayPal['id']));
            $oldStatus = $getCurrentPayPalOrderStatusQueryResult->getStatus();
        } else {
            $oldStatus = $cacheOrderPayPal['status'];
        }

        if (
            $this->paypalOrderComparator->compare($orderPayPal)
            && $this->checkTransitionPayPalOrderStatusService->checkAvailableStatus(
                $oldStatus,
                $orderPayPal['status']
            )
        ) {
            $module->getLogger()->debug(
                'Need to dispatch PayPalOrderEvent',
                [
                    'PayPalOrderId' => $orderPayPal['id'],
                    'CurrentPayPalOrderStatus' => $oldStatus,
                    'NewPayPalOrderStatus' => $orderPayPal['status'],
                ]
            );
            $this->dispatchAfterCheck($orderPayPal);
        } else {
            $module->getLogger()->debug(
                'No need to dispatch PayPalOrderEvent',
                [
                    'PayPalOrderId' => $orderPayPal['id'],
                    'CurrentPayPalOrderStatus' => $oldStatus,
                    'NewPayPalOrderStatus' => $orderPayPal['status'],
                ]
            );
        }
    }

    /**
     * @throws Exception\PayPalOrderException
     */
    private function dispatchAfterCheck(array $orderPayPal)
    {
        switch ($orderPayPal['status']) {
            case PayPalOrderStatus::APPROVED:
                $this->eventDispatcher->dispatch(new PayPalOrderApprovedEvent($orderPayPal['id'], $orderPayPal));
                break;
            case PayPalOrderStatus::COMPLETED:
                $this->eventDispatcher->dispatch(new PayPalOrderCompletedEvent($orderPayPal['id'], $orderPayPal));
                break;
            case PayPalOrderStatus::CREATED:
                $this->eventDispatcher->dispatch(new PayPalOrderCreatedEvent($orderPayPal['id'], $orderPayPal));
                break;
            case PayPalOrderStatus::SAVED:
                $this->eventDispatcher->dispatch(new PayPalOrderSavedEvent($orderPayPal['id'], $orderPayPal, $orderPayPal['update_time']));
                break;
            case PayPalOrderStatus::PAYER_ACTION_REQUIRED:
            case PayPalOrderStatus::PENDING_APPROVAL:
            case PayPalOrderStatus::VOIDED:
                break;
        }
    }
}

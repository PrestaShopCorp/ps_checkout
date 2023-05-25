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

namespace PrestaShop\Module\PrestashopCheckout\PayPal\Payment\Capture;

use PrestaShop\Module\PrestashopCheckout\CommandBus\CommandBusInterface;
use PrestaShop\Module\PrestashopCheckout\Event\EventDispatcherInterface;
use PrestaShop\Module\PrestashopCheckout\Order\Exception\OrderException;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Cache\CacheInterface;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\CheckTransitionPayPalOrderStatusService;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Comparator\PayPalOrderComparator;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Event\PayPalOrderApprovedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Event\PayPalOrderCompletedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Exception\PayPalOrderException;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Query\GetCurrentPayPalOrderStatusQuery;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Query\GetCurrentPayPalOrderStatusQueryResult;
use PrestaShop\Module\PrestashopCheckout\PayPal\Payment\Capture\Comparator\PayPalCaptureComparator;
use PrestaShop\Module\PrestashopCheckout\PayPal\Payment\Capture\Event\PayPalCaptureCompletedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Payment\Capture\Event\PayPalCaptureDeclinedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Payment\Capture\Event\PayPalCapturePendingEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Payment\Capture\Event\PayPalCaptureRefundedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Payment\Capture\Event\PayPalCaptureReversedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Payment\Capture\Exception\PayPalCaptureException;
use Psr\SimpleCache\InvalidArgumentException;

class PayPalCaptureEventDispatcher
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
     * @var CheckTransitionPayPalCaptureStatusService
     */
    private $checkTransitionPayPalCaptureStatusService;

    /**
     * @var PayPalCaptureComparator
     */
    private $paypalCaptureComparator;

    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @param EventDispatcherInterface $eventDispatcher
     * @param CommandBusInterface $commandBus
     * @param CheckTransitionPayPalCaptureStatusService $checkTransitionPayPalCaptureStatusService
     * @param PayPalCaptureComparator $payPalCaptureComparator
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        CommandBusInterface $commandBus,
        CheckTransitionPayPalCaptureStatusService $checkTransitionPayPalCaptureStatusService,
        PayPalOrderComparator $paypalCaptureComparator,
        CacheInterface $cache
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->commandBus = $commandBus;
        $this->checkTransitionPayPalCaptureStatusService = $checkTransitionPayPalCaptureStatusService;
        $this->paypalCaptureComparator = $paypalCaptureComparator;
        $this->cache = $cache;
    }

    /**
     * @param array{id: string, status: string} $orderPayPal
     *
     * @return void
     * @throws OrderException
     * @throws InvalidArgumentException
     */
    public function dispatch(array $payload)
    {
        $orderPayPal = $payload['resource'];
        $capture = $this->cache->get($orderPayPal['id']);
        if($capture === null)
        {
            $this->dispatchAfterCheck($payload);
        } else if (
            $this->paypalCaptureComparator->compare($orderPayPal)
            && $this->checkTransitionPayPalCaptureStatusService->checkAvailableStatus(
                $capture['status'],
                $orderPayPal['status']
            )
        ) {
            $this->dispatchAfterCheck($payload);
        }
    }

    /**
     * @throws PayPalCaptureException
     * @throws PayPalOrderException
     */
    private function dispatchAfterCheck(array $payload){
        switch ($payload['resource']['status']) {
            case PayPalCaptureStatus::COMPLETED:
                $this->eventDispatcher->dispatch(new PayPalCaptureCompletedEvent($payload['resource']['id'],$payload['orderId'], $payload['resource']));
                break;
            case PayPalCaptureStatus::DECLINED:
                $this->eventDispatcher->dispatch(new PayPalCaptureDeclinedEvent($payload['resource']['id'],$payload['orderId'], $payload['resource']));
                break;
            case PayPalCaptureStatus::PENDING:
                $this->eventDispatcher->dispatch(new PayPalCapturePendingEvent($payload['resource']['id'],$payload['orderId'], $payload['resource']));
                break;
            case PayPalCaptureStatus::REFUND:
                $this->eventDispatcher->dispatch(new PayPalCaptureRefundedEvent($payload['resource']['id'],$payload['orderId'], $payload['resource']));
                break;
            case PayPalCaptureStatus::REVERSED:
                $this->eventDispatcher->dispatch(new PayPalCaptureReversedEvent($payload['resource']['id'],$payload['orderId'], $payload['resource']));
                break;
        }
    }
}

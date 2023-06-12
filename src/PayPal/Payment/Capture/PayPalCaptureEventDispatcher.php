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

use PrestaShop\Module\PrestashopCheckout\Event\EventDispatcherInterface;
use PrestaShop\Module\PrestashopCheckout\Order\Exception\OrderException;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Exception\PayPalOrderException;
use PrestaShop\Module\PrestashopCheckout\PayPal\Payment\Capture\Comparator\PayPalCaptureComparator;
use PrestaShop\Module\PrestashopCheckout\PayPal\Payment\Capture\Event\PayPalCaptureCompletedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Payment\Capture\Event\PayPalCaptureDeclinedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Payment\Capture\Event\PayPalCapturePendingEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Payment\Capture\Event\PayPalCaptureRefundedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Payment\Capture\Event\PayPalCaptureReversedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Payment\Capture\Exception\PayPalCaptureException;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;

class PayPalCaptureEventDispatcher
{
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

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
    private $capturePayPalCache;

    /**
     * @param EventDispatcherInterface $eventDispatcher
     * @param CheckTransitionPayPalCaptureStatusService $checkTransitionPayPalCaptureStatusService
     * @param PayPalCaptureComparator $paypalCaptureComparator
     * @param CacheInterface $capturePayPalCache
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        CheckTransitionPayPalCaptureStatusService $checkTransitionPayPalCaptureStatusService,
        PayPalCaptureComparator $paypalCaptureComparator,
        CacheInterface $capturePayPalCache
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->checkTransitionPayPalCaptureStatusService = $checkTransitionPayPalCaptureStatusService;
        $this->paypalCaptureComparator = $paypalCaptureComparator;
        $this->capturePayPalCache = $capturePayPalCache;
    }

    /**
     * @param string $orderId
     * @param array $capture
     *
     * @return void
     *
     * @throws InvalidArgumentException
     * @throws OrderException
     * @throws PayPalCaptureException
     * @throws PayPalOrderException
     */
    public function dispatch($orderId, array $capture)
    {
        $module = \Module::getInstanceByName('ps_checkout');

        $cacheCapturePayPal = $this->capturePayPalCache->get($capture['id']);

        if (empty($cacheCapturePayPal)) {
            $this->dispatchAfterCheck($orderId, $capture);
        } elseif (
            !$this->paypalCaptureComparator->compare($capture)
            && $this->checkTransitionPayPalCaptureStatusService->checkAvailableStatus(
                $cacheCapturePayPal['status'],
                $capture['status']
            )
        ) {
            $module->getLogger()->debug(
                'Need to dispatch PayPalCaptureEvent',
                [
                    'PayPalOrderId' => $orderId,
                    'PayPalCaptureId' => $capture['id'],
                    'CurrentPayPalCaptureStatus' => $cacheCapturePayPal['status'],
                    'NewPayPalCaptureStatus' => $capture['status'],
                ]
            );
            $this->dispatchAfterCheck($orderId, $capture);
        } else {
            $module->getLogger()->debug(
                'No need to dispatch PayPalCaptureEvent',
                [
                    'PayPalOrderId' => $orderId,
                    'PayPalCaptureId' => $capture['id'],
                    'CurrentPayPalCaptureStatus' => $cacheCapturePayPal['status'],
                    'NewPayPalCaptureStatus' => $capture['status'],
                ]
            );
        }
    }

    /**
     * @param string $orderId
     * @param array $capture
     *
     * @return void
     *
     * @throws PayPalCaptureException
     * @throws PayPalOrderException
     */
    private function dispatchAfterCheck($orderId, array $capture)
    {
        switch ($capture['status']) {
            case PayPalCaptureStatus::COMPLETED:
                $this->eventDispatcher->dispatch(new PayPalCaptureCompletedEvent($capture['id'], $orderId, $capture));
                break;
            case PayPalCaptureStatus::DECLINED:
                $this->eventDispatcher->dispatch(new PayPalCaptureDeclinedEvent($capture['id'], $orderId, $capture));
                break;
            case PayPalCaptureStatus::PENDING:
                $this->eventDispatcher->dispatch(new PayPalCapturePendingEvent($capture['id'], $orderId, $capture));
                break;
            case PayPalCaptureStatus::REFUND:
                $this->eventDispatcher->dispatch(new PayPalCaptureRefundedEvent($capture['id'], $orderId, $capture));
                break;
            case PayPalCaptureStatus::REVERSED:
                $this->eventDispatcher->dispatch(new PayPalCaptureReversedEvent($capture['id'], $orderId, $capture));
                break;
        }
    }
}

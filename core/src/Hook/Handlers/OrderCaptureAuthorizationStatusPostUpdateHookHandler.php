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

namespace PsCheckout\Core\Hook\Handlers;

use Exception;
use Order;
use PsCheckout\Core\OrderState\Configuration\OrderStateConfiguration;
use PsCheckout\Core\PayPal\Order\Configuration\PayPalOrderIntent;
use PsCheckout\Core\PayPal\Order\Provider\PayPalOrderProviderInterface;
use PsCheckout\Core\PayPal\Order\Repository\PayPalOrderRepositoryInterface;
use PsCheckout\Core\PayPal\Payment\Authorization\Action\CaptureAuthorizationAction;
use PsCheckout\Infrastructure\Adapter\Configuration;
use Psr\Log\LoggerInterface;

/**
 * @implements HookHandlerInterface<OrderCaptureAuthorizationStatusPostUpdateHookParams>
 */
class OrderCaptureAuthorizationStatusPostUpdateHookHandler implements HookHandlerInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var PayPalOrderRepositoryInterface
     */
    private $paypalOrderRepository;

    /**
     * @var PayPalOrderProviderInterface
     */
    private $paypalOrderProvider;

    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @var CaptureAuthorizationAction
     */
    private $captureAuthorizationAction;

    public function __construct(
        LoggerInterface              $logger,
        PaypalOrderRepositoryInterface $paypalOrderRepository,
        PayPalOrderProviderInterface $paypalOrderProvider,
        Configuration                $configuration,
        CaptureAuthorizationAction   $captureAuthorizationAction
    ) {
        $this->logger = $logger;
        $this->paypalOrderRepository = $paypalOrderRepository;
        $this->paypalOrderProvider = $paypalOrderProvider;
        $this->configuration = $configuration;
        $this->captureAuthorizationAction = $captureAuthorizationAction;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(HookParamsInterface $params): ?HookHandlerResult
    {
        if (PHP_SAPI === 'cli') {
            $this->logger->error('Order status post update hook is not supported in CLI mode');

            return null;
        }

        $order = new Order($params->getOrderId());
        if ($order->module !== 'ps_checkout') {
            $this->logger->info('Order status post update hook is not supported for this module');

            return null;
        }

        try {
            $payPalOrder = $this->paypalOrderRepository->getOneByCartId($order->id_cart);
            if (!$payPalOrder) {
                $this->logger->error('PayPal order not found for order ID: ' . $params->getOrderId());

                throw new Exception('PayPal order not found for order ID: ' . $params->getOrderId());
            }
            $payPalOrderResponse = $this->paypalOrderProvider->getById($payPalOrder->getId());
        } catch (Exception $exception) {
            $this->logger->error('Failed to fetch PayPal order for order ID: ' . $params->getOrderId(), ['exception' => $exception]);

            return new HookHandlerResult(true, 'There was an error during the payment. Please try again or contact the support.');
        }

        if ($payPalOrderResponse->getIntent() !== PayPalOrderIntent::AUTHORIZE) {
            return null;
        }

        $statuses = $this->configuration->get(OrderStateConfiguration::PS_CHECKOUT_AUTHORIZE_STATES);
        if (empty($statuses)) {
            $this->logger->info('No order statuses are configured for the capture of the authorization.', [
                'order_id' => $params->getOrderId(),
                'order_status_id' => $params->getNewOrderStatus()->id,
            ]);

            return null;
        }

        $captureOrderStatusIds = explode(',', $statuses);
        if (!in_array((string) $params->getNewOrderStatus()->id, $captureOrderStatusIds, true)) {
            $this->logger->info('Order status does not match any of the configured capture order statuses.', [
                'order_id' => $params->getOrderId(),
                'order_status_id' => $params->getNewOrderStatus()->id,
                'capture_order_status_ids' => $captureOrderStatusIds,
            ]);

            return null;
        }

        try {
            $this->captureAuthorizationAction->execute($payPalOrderResponse);

            return new HookHandlerResult(false, 'The authorization has been successfully captured.');
        } catch (Exception $exception) {
            $this->logger->error('Failed to capture authorization for order ID: ' . $params->getOrderId(), ['exception' => $exception]);

            return new HookHandlerResult(true, 'An error occurred during the capture of the authorization.');
        }
    }
}

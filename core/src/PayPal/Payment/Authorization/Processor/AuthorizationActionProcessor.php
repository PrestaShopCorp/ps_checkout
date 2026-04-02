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

namespace PsCheckout\Core\PayPal\Payment\Authorization\Processor;

use Exception;
use PsCheckout\Core\PayPal\Order\Provider\PayPalOrderProviderInterface;
use PsCheckout\Core\PayPal\Payment\Authorization\Action\AuthorizationActionInterface;
use Psr\Log\LoggerInterface;

class AuthorizationActionProcessor implements AuthorizationActionProcessorInterface
{
    /**
     * @var iterable<AuthorizationActionInterface>
     */
    private $handlers;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var PayPalOrderProviderInterface
     */
    private $payPalOrderProvider;

    /**
     * @param AuthorizationActionInterface[] $handlers
     * @param PayPalOrderProviderInterface $payPalOrderProvider
     * @param LoggerInterface $logger
     */
    public function __construct(
        iterable $handlers,
        PayPalOrderProviderInterface $payPalOrderProvider,
        LoggerInterface $logger
    ) {
        $this->handlers = $handlers;
        $this->payPalOrderProvider = $payPalOrderProvider;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function process(string $action, ?string $orderId, array $payload = []): array
    {
        if (!$orderId) {
            return [
                'httpCode' => 400,
                'status' => false,
            ];
        }

        $handler = $this->findHandler($action);
        if (!$handler) {
            return [
                'httpCode' => 400,
                'status' => false,
            ];
        }

        try {
            $payPalOrderResponse = $this->payPalOrderProvider->getById($orderId);

            $handler->execute($payPalOrderResponse, $payload);
        } catch (Exception $e) {
            $this->logger->error('Failed to execute authorization action: ' . $e->getMessage());

            return [
                'httpCode' => 500,
                'status' => false,
                'error' => [
                    'message' => $e->getMessage(),
                    'code' => $e->getCode(),
                ],
            ];
        }

        return [
            'status' => true,
        ];
    }

    /**
     * Find the handler that supports the given action.
     *
     * @param string $action
     *
     * @return AuthorizationActionInterface|null
     */
    private function findHandler(string $action): ?AuthorizationActionInterface
    {
        foreach ($this->handlers as $handler) {
            if ($handler->supports($action)) {
                return $handler;
            }
        }

        return null;
    }
}

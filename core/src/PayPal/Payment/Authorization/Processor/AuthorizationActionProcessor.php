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
use PsCheckout\Core\PayPal\Order\Action\CaptureAuthorizationActionInterface;
use PsCheckout\Core\PayPal\Order\Action\VoidAuthorizationActionInterface;
use PsCheckout\Core\PayPal\Order\Provider\PayPalOrderProviderInterface;
use PsCheckout\Core\PayPal\Payment\Authorization\Action\ReauthorizeAuthorizationActionInterface;
use Psr\Log\LoggerInterface;

class AuthorizationActionProcessor implements AuthorizationActionProcessorInterface
{
    /**
     * @var array{
     *     'void': VoidAuthorizationActionInterface,
     *     'reauthorize': ReauthorizeAuthorizationActionInterface,
     *     'capture': CaptureAuthorizationActionInterface
     * }
     */
    private $processors;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var PayPalOrderProviderInterface
     */
    private $payPalOrderProvider;

    public function __construct(
        VoidAuthorizationActionInterface $voidAuthorizationAction,
        ReauthorizeAuthorizationActionInterface $reauthorizeAuthorizationAction,
        CaptureAuthorizationActionInterface $captureAuthorizationAction,
        PayPalOrderProviderInterface $payPalOrderProvider,
        LoggerInterface $logger
    ) {
        $this->processors = [
            'capture' => $captureAuthorizationAction,
            'void' => $voidAuthorizationAction,
            'reauthorize' => $reauthorizeAuthorizationAction
        ];
        $this->payPalOrderProvider = $payPalOrderProvider;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function process(string $action, ?string $orderId): array
    {
        if (!$orderId || !array_key_exists($action, $this->processors)) {
            return [
                'httpCode' => 400,
                'status' => false,
            ];
        }

        try {
            $payPalOrderResponse = $this->payPalOrderProvider->getById($orderId);

            $handler = $this->processors[$action];

            $handler->execute($payPalOrderResponse);
        } catch (Exception $e) {
            $this->logger->error('Failed to void authorization: ' . $e->getMessage());

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
}

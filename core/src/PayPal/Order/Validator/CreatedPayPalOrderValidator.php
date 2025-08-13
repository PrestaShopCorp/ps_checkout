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

namespace PsCheckout\Core\PayPal\Order\Validator;

use PsCheckout\Core\PayPal\Order\Configuration\PayPalCaptureStatus;
use PsCheckout\Core\PayPal\Order\Provider\PayPalOrderProviderInterface;
use PsCheckout\Core\PayPal\Order\ValueObject\PayPalOrderCompletionData;
use PsCheckout\Infrastructure\Adapter\CartInterface;
use PsCheckout\Infrastructure\Repository\OrderRepositoryInterface;

class CreatedPayPalOrderValidator implements CreatedPayPalOrderValidatorInterface
{
    /**
     * @var PayPalOrderProviderInterface
     */
    private $payPalOrderProvider;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var CartInterface
     */
    private $cart;

    /**
     * @var int
     */
    private $moduleId;

    public function __construct(
        PayPalOrderProviderInterface $payPalOrderProvider,
        OrderRepositoryInterface $orderRepository,
        CartInterface $cart,
        int $moduleId
    ) {
        $this->payPalOrderProvider = $payPalOrderProvider;
        $this->orderRepository = $orderRepository;
        $this->cart = $cart;
        $this->moduleId = $moduleId;
    }

    /**
     * {@inheritdoc}
     */
    public function validate(string $payPalOrderId, int $cartId)
    {
        $order = $this->orderRepository->getOneBy(['id_cart' => $cartId]);

        //NOTE: order must be created in shop
        if (!$order) {
            return null;
        }

        $paypalOrderResponse = $this->payPalOrderProvider->getById($payPalOrderId);

        //NOTE: order must exist in paypal and have correct status
        if ($paypalOrderResponse->getStatus() !== PayPalCaptureStatus::PENDING
            && $paypalOrderResponse->getStatus() !== PayPalCaptureStatus::COMPLETED
        ) {
            return null;
        }

        return new PayPalOrderCompletionData(
            $paypalOrderResponse->getStatus(),
            $paypalOrderResponse->getId(),
            $paypalOrderResponse->getCapture()['id'],
            $cartId,
            $this->moduleId,
            (int) $order->id,
            $this->cart->getCart($cartId)->secure_key
        );
    }
}

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


namespace PsCheckout\Core\PayPal\ShippingTracking\Validator;

use Order;
use Carrier;
use Validate;
use PsCheckout\Core\Exception\PsCheckoutException;
use PsCheckout\Core\PayPal\Order\Repository\PayPalOrderRepositoryInterface;
use PsCheckout\Core\PayPal\Order\Repository\PayPalOrderCaptureRepositoryInterface;

class OrderTrackerValidator implements OrderTrackerValidatorInterface
{
    /**
     * Capture statuses that allow tracking
     */
    const ALLOWED_CAPTURE_STATUSES = ['COMPLETED', 'PARTIALLY_REFUNDED', 'PENDING'];

    /**
     * @var PayPalOrderRepositoryInterface
     */
    private $payPalOrderRepository;

    /**
     * @var PayPalOrderCaptureRepositoryInterface
     */
    private $payPalOrderCaptureRepository;

    public function __construct(
        PayPalOrderRepositoryInterface $payPalOrderRepository,
        PayPalOrderCaptureRepositoryInterface $payPalOrderCaptureRepository
    ) {
        $this->payPalOrderRepository = $payPalOrderRepository;
        $this->payPalOrderCaptureRepository = $payPalOrderCaptureRepository;
    }

    /**
     * {@inheritDoc}
     */
    public function validate(Order $order, Carrier $carrier): array
    {
        // 1. Validate ps_checkout order
        if (!Validate::isLoadedObject($order) || $order->module !== 'ps_checkout') {
            throw new PsCheckoutException('Order is not a ps_checkout order');
        }

        // 2. Get PayPal order
        $payPalOrder = $this->getPayPalOrderByCart($order->id_cart);
        if (!$payPalOrder) {
            throw new PsCheckoutException('PayPal order not found for cart');
        }

        // 3. Get valid capture
        $capture = $this->getValidCapture($payPalOrder->getId());
        if (!$capture) {
            throw new PsCheckoutException('No valid capture found for PayPal order');
        }

        return [
            'paypal_order' => $payPalOrder,
            'capture' => $capture
        ];
    }

    /**
     * Get PayPal order by cart ID
     *
     * @param int $cartId
     *
     * @return \PsCheckout\Core\PayPal\Order\Entity\PayPalOrder|null
     *
     * @throws PsCheckoutException
     */
    private function getPayPalOrderByCart(int $cartId)
    {
        try {
            return $this->payPalOrderRepository->getOneByCartId($cartId);
        } catch (\Exception $exception) {
            throw new PsCheckoutException('Failed to get PayPal order by cart ID: ' . $exception->getMessage());
        }
    }

    /**
     * Get valid capture for PayPal order
     *
     * @param string $payPalOrderId
     *
     * @return \PsCheckout\Core\PayPal\Order\Entity\PayPalOrderCapture|null
     *
     * @throws PsCheckoutException
     */
    private function getValidCapture(string $payPalOrderId)
    {
        try {
            $captures = $this->payPalOrderCaptureRepository->getByOrderId($payPalOrderId);
            
            if (empty($captures)) {
                return null;
            }
            
            foreach ($captures as $capture) {
                if (in_array($capture->getStatus(), self::ALLOWED_CAPTURE_STATUSES, true)) {
                    return $capture;
                }
            }

            return null;
        } catch (\Exception $exception) {
            throw new PsCheckoutException('Failed to get capture for PayPal order: ' . $exception->getMessage());
        }
    }
}

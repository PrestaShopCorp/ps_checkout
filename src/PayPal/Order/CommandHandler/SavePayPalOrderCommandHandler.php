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

namespace PrestaShop\Module\PrestashopCheckout\PayPal\Order\CommandHandler;

use PrestaShop\Module\PrestashopCheckout\Entity\PayPalOrder;
use PrestaShop\Module\PrestashopCheckout\Entity\PayPalOrderAuthorization;
use PrestaShop\Module\PrestashopCheckout\Entity\PayPalOrderCapture;
use PrestaShop\Module\PrestashopCheckout\Entity\PayPalOrderPurchaseUnit;
use PrestaShop\Module\PrestashopCheckout\Entity\PayPalOrderRefund;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\ValueObject\PayPalOrderId;
use PrestaShop\Module\PrestashopCheckout\Repository\PayPalOrderRepository;
use PrestaShop\PrestaShop\Core\Foundation\Database\EntityNotFoundException;
use src\PayPal\Order\Command\SavePayPalOrderCommand;

class SavePayPalOrderCommandHandler
{
    /**
     * @var PayPalOrderRepository
     */
    private $payPalOrderRepository;

    public function __construct(PayPalOrderRepository $payPalOrderRepository)
    {
        $this->payPalOrderRepository = $payPalOrderRepository;
    }

    public function handle(SavePayPalOrderCommand $command)
    {
        $order = $command->getOrder();

        try {
            $payPalOrder = $this->payPalOrderRepository->getPayPalOrderById(new PayPalOrderId($order['id']));
            $payPalOrder->setStatus($order['status'])
            ->setPaymentSource($order['payment_source']);
            $this->payPalOrderRepository->updatePayPalOrder($payPalOrder);
        } catch (EntityNotFoundException $exception) {
            $payPalOrder = new PayPalOrder(
                $order['id'],
                $command->getCartId()->getValue(),
                $order['intent'],
                array_keys($order['payment_source'])[0],
                $order['status'],
                $order['payment_source'],
                $command->getPaymentMode(),
                $command->isCardFields(),
                $command->isExpressCheckout(),
                $command->getCustomerIntent()
            );
            $this->payPalOrderRepository->createPayPalOrder($payPalOrder);
        }

        if (!empty($order['purchase_units'])) {
            foreach ($order['purchase_units'] as $purchaseUnit) {
                $payPalPurchaseUnit = new PayPalOrderPurchaseUnit(
                    $order['id'],
                    crc32(json_encode($purchaseUnit)),
                    $purchaseUnit['reference_id'],
                    $purchaseUnit['items']
                );

                $this->payPalOrderRepository->createPayPalOrderPurchaseUnit($payPalPurchaseUnit);

                if (!empty($purchaseUnit['payments']['captures'])) {
                    foreach ($purchaseUnit['payments']['captures'] as $capture) {
                        $payPalCapture = new PayPalOrderCapture(
                            $capture['id'],
                            $order['id'],
                            $capture['status'],
                            $capture['create_time'],
                            $capture['update_time'],
                            $capture['seller_protection'],
                            $capture['seller_receivable_breakdown'],
                            (bool) $capture['final_capture']
                        );
                        $this->payPalOrderRepository->createPayPalOrderCapture($payPalCapture);
                    }
                }

                if (!empty($purchaseUnit['payments']['authorizations'])) {
                    foreach ($purchaseUnit['payments']['authorizations'] as $authorization) {
                        $payPalAuthorization = new PayPalOrderAuthorization(
                            $authorization['id'],
                            $order['id'],
                            $authorization['status'],
                            $authorization['expiration_time'],
                            $authorization['seller_protection']['status']
                        );
                        $this->payPalOrderRepository->createPayPalOrderAuthorization($payPalAuthorization);
                    }
                }

                if (!empty($purchaseUnit['payments']['refunds'])) {
                    foreach ($purchaseUnit['payments']['refunds'] as $refund) {
                        $payPalRefund = new PayPalOrderRefund(
                            $refund['id'],
                            $order['id'],
                            $refund['status'],
                            $refund['invoice_id'],
                            $refund['custom_id'],
                            $refund['acquirer_reference_number'],
                            $refund['seller_payable_breakdown']
                        );
                        $this->payPalOrderRepository->createPayPalOrderRefund($payPalRefund);
                    }
                }
            }
        }
    }
}

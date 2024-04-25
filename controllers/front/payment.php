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

use PrestaShop\Module\PrestashopCheckout\CommandBus\CommandBusInterface;
use PrestaShop\Module\PrestashopCheckout\Controller\AbstractFrontController;
use PrestaShop\Module\PrestashopCheckout\Exception\PsCheckoutException;
use PrestaShop\Module\PrestashopCheckout\Order\Command\CreateOrderCommand;
use PrestaShop\Module\PrestashopCheckout\PayPal\Card3DSecure;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Command\CapturePayPalOrderCommand;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Entity\PayPalOrder;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Exception\PayPalOrderException;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\ValueObject\PayPalOrderId;
use PrestaShop\Module\PrestashopCheckout\PayPal\PayPalOrderProvider;
use PrestaShop\Module\PrestashopCheckout\Repository\PaymentTokenRepository;
use PrestaShop\Module\PrestashopCheckout\Repository\PayPalOrderRepository;

class Ps_CheckoutPaymentModuleFrontController extends AbstractFrontController
{
    public $ssl = true;

    public $controller_type = 'front';

    public $display_footer = false;

    public $display_header = true;

    private $orderPageUrl;

    /**
     * @var PayPalOrderId
     */
    private $paypalOrderId;

    public function checkAccess()
    {
        return $this->context->customer && $this->context->customer->isLogged() && $this->context->cart;
    }

    public function initContent()
    {
        $this->orderPageUrl = $this->context->link->getPageLink('order');
        parent::initContent();
        $this->setTemplate('module:ps_checkout/views/templates/front/payment.tpl');
        $this->context->smarty->assign('css_url', $this->module->getPathUri() . 'views/css/payment.css');
        $this->context->smarty->assign('order_url', $this->orderPageUrl);
    }

    public function setMedia()
    {
        $this->registerStylesheet('ps_checkout_payment', '/modules/ps_checkout/views/css/payment.css');
        parent::setMedia();
    }

    public function postProcess()
    {
        $orderId = Tools::getValue('orderID');

        if (!$orderId) {
            $this->redirectToOrderPage();
        }

        try {
            $this->paypalOrderId = new PayPalOrderId($orderId);

            /** @var PayPalOrderRepository $payPalOrderRepository */
            $payPalOrderRepository = $this->module->getService(PayPalOrderRepository::class);
            /** @var PayPalOrderProvider $payPalOrderProvider */
            $payPalOrderProvider = $this->module->getService(PayPalOrderProvider::class);
            /** @var CommandBusInterface $commandBus */
            $commandBus = $this->module->getService('ps_checkout.bus.command');
            /** @var Psr\SimpleCache\CacheInterface $payPalOrderCache */
            $payPalOrderCache = $this->module->getService('ps_checkout.cache.paypal.order');

            $payPalOrder = $payPalOrderRepository->getPayPalOrderById($this->paypalOrderId);

            if ($payPalOrder->getIdCart() !== $this->context->cart->id) {
                throw new Exception('PayPal order does not belong to this customer');
            }

            $payPalOrderFromCache = $payPalOrderProvider->getById($payPalOrder->getId()->getValue());

            if ($payPalOrderFromCache['status'] === 'COMPLETED') {
                $this->createOrder($payPalOrderFromCache, $payPalOrder);
            }

            if ($payPalOrderFromCache['status'] === 'PAYER_ACTION_REQUIRED') {
                // Delete from cache so when user is redirected from 3DS authentication page the order is fetched from PayPal
                if ($payPalOrderCache->has($this->paypalOrderId->getValue())) {
                    $payPalOrderCache->delete($this->paypalOrderId->getValue());
                }

                $this->redirectTo3DSVerification($payPalOrderFromCache);
            }

            // WHEN 3DS fails
            if ($payPalOrderFromCache['status'] === 'CREATED') {
                $card3DSecure = new Card3DSecure();
                switch ($card3DSecure->continueWithAuthorization($payPalOrderFromCache)) {
                    case Card3DSecure::RETRY:
                        $this->redirectTo3DSVerification($payPalOrderFromCache);
                        break;
                    case Card3DSecure::PROCEED:
                        $commandBus->handle(new CapturePayPalOrderCommand($this->paypalOrderId->getValue(), array_keys($payPalOrderFromCache['payment_source'])[0]));
                        $payPalOrderFromCache = $payPalOrderCache->get($this->paypalOrderId->getValue());
                        $this->createOrder($payPalOrderFromCache, $payPalOrder);
                        break;
                    case Card3DSecure::NO_DECISION:
                    default:
                        break;
                }
            }
        } catch (Exception $exception) {
            $this->context->smarty->assign('error', $exception->getMessage());
        }
    }

    /**
     * @param array $payPalOrderFromCache
     * @param PayPalOrder$payPalOrder
     *
     * @return void
     *
     * @throws PrestaShopException
     * @throws PsCheckoutException
     * @throws PayPalOrderException
     */
    private function createOrder($payPalOrderFromCache, $payPalOrder)
    {
        /** @var CommandBusInterface $commandBus */
        $commandBus = $this->module->getService('ps_checkout.bus.command');

        $capture = $payPalOrderFromCache['purchase_units'][0]['payments']['captures'][0];
        if ($capture['status'] === 'COMPLETED') {
            $commandBus->handle(new CreateOrderCommand($payPalOrder->getId()->getValue(), $capture));
            if ($payPalOrder->getPaymentTokenId() && $payPalOrder->checkCustomerIntent(PayPalOrder::CUSTOMER_INTENT_FAVORITE)) {
                /** @var PaymentTokenRepository $paymentTokenRepository */
                $paymentTokenRepository = $this->module->getService(PaymentTokenRepository::class);
                $paymentTokenRepository->setTokenFavorite($payPalOrder->getPaymentTokenId());
            }
            $this->redirectToOrderConfirmationPage($payPalOrder->getIdCart(), $capture['id'], $payPalOrderFromCache['status']);
        }
    }

    /**
     * @param array $order
     *
     * @return void
     */
    private function redirectTo3DSVerification($order)
    {
        $payerActionLinks = array_filter($order['links'], function ($link) {
            return $link['rel'] === 'payer-action';
        });
        if (!empty($payerActionLinks)) {
            Tools::redirect(reset($payerActionLinks)['href'] . '&redirect_uri=' . urlencode($this->context->link->getModuleLink('ps_checkout', 'payment', ['orderID' => $this->paypalOrderId->getValue()])));
        }
    }

    private function redirectToOrderPage()
    {
        Tools::redirect($this->orderPageUrl);
    }

    /**
     * @param int $cartId
     * @param string $captureId
     * @param string $payPalOrderStatus
     *
     * @return void
     *
     * @throws PrestaShopException
     */
    private function redirectToOrderConfirmationPage($cartId, $captureId, $payPalOrderStatus)
    {
        $orders = new PrestaShopCollection(Order::class);
        $orders->where('id_cart', '=', $cartId);

        if (!$orders->count()) {
            return;
        }

        /** @var Order $order */
        $order = $orders->getFirst();

        $cart = new Cart($cartId);

        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            Tools::redirect($this->context->link->getPageLink(
                'order-confirmation',
                true,
                (int) $order->id_lang,
                [
                    'paypal_status' => $payPalOrderStatus,
                    'paypal_order' => $this->paypalOrderId->getValue(),
                    'paypal_transaction' => $captureId,
                    'id_cart' => $cartId,
                    'id_module' => (int) $this->module->id,
                    'id_order' => (int) $order->id,
                    'key' => $cart->secure_key,
                ]
            ));
        }
    }
}

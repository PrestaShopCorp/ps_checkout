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
if (!defined('_PS_VERSION_')) {
    exit;
}

use PsCheckout\Api\ValueObject\PayPalOrderResponse;
use PsCheckout\Core\Exception\PsCheckoutException;
use PsCheckout\Core\Order\Action\CreateOrderAction;
use PsCheckout\Core\PayPal\Card3DSecure\Card3DSecureConfiguration;
use PsCheckout\Core\PayPal\Card3DSecure\Card3DSecureValidator;
use PsCheckout\Core\PayPal\Order\Action\CapturePayPalOrderAction;
use PsCheckout\Core\PayPal\Order\Configuration\PayPalOrderStatus;
use PsCheckout\Core\PayPal\Order\Entity\PayPalOrder;
use PsCheckout\Core\PayPal\Order\Exception\PayPalOrderException;
use PsCheckout\Core\PayPal\Order\Provider\PayPalOrderProvider;
use PsCheckout\Core\Settings\Configuration\PayPalConfiguration;
use PsCheckout\Infrastructure\Adapter\Tools;
use PsCheckout\Infrastructure\Controller\AbstractFrontController;
use PsCheckout\Infrastructure\Repository\OrderRepository;
use PsCheckout\Infrastructure\Repository\PaymentTokenRepository;
use PsCheckout\Infrastructure\Repository\PayPalOrderRepository;
use Psr\Log\LoggerInterface;

class Ps_CheckoutPaymentModuleFrontController extends AbstractFrontController
{
    public $ssl = true;

    public $controller_type = 'front';

    public $display_footer = false;

    public $display_header = true;

    private $orderPageUrl;

    private $paypalOrderId;

    /**
     * @return bool
     */
    public function checkAccess(): bool
    {
        return $this->context->customer && $this->context->cart;
    }

    /**
     * @return void
     *
     * @throws PrestaShopException
     */
    public function initContent()
    {
        $this->orderPageUrl = $this->context->link->getPageLink('order');
        parent::initContent();
        $this->setTemplate('module:' . $this->module->name . '/views/templates/front/payment.tpl');

        $this->context->smarty->assign([
            'css_url' => $this->module->getPathUri() . 'views/css/payment.css',
            'order_url' => $this->orderPageUrl,
        ]);
    }

    /**
     * @return bool|void
     */
    public function setMedia()
    {
        $this->registerStylesheet('ps_checkout_payment', '/modules/' . $this->module->name . '/views/css/payment.css');
        parent::setMedia();
    }

    /**
     * @return void
     */
    public function postProcess()
    {
        /** @var Tools $tools */
        $tools = $this->module->getService(Tools::class);
        /** @var LoggerInterface $logger */
        $logger = $this->module->getService(LoggerInterface::class);

        $orderId = $tools->getValue('orderID');
        if (!$orderId) {
            $logger->error('No PayPal Order ID found.');
            $tools->redirect($this->orderPageUrl);
        }

        $this->paypalOrderId = $orderId;
        $logger->info('Processing PayPal Order ID: ' . $this->paypalOrderId);

        try {
            /** @var PayPalOrderRepository $payPalOrderRepository */
            $payPalOrderRepository = $this->module->getService(PayPalOrderRepository::class);

            /** @var PayPalOrderProvider $payPalOrderProvider */
            $payPalOrderProvider = $this->module->getService(PayPalOrderProvider::class);

            /** @var OrderRepository $orderRepository */
            $orderRepository = $this->module->getService(OrderRepository::class);

            $payPalOrder = $payPalOrderRepository->getOneBy(['id' => $this->paypalOrderId]);

            if (!$payPalOrder) {
                $logger->error('PayPal Order not found: ' . $this->paypalOrderId);
                $tools->redirect($this->orderPageUrl);
            }

            $orders = $orderRepository->getAllBy(['id_cart' => $payPalOrder->getIdCart()]);

            if (!empty($orders)) {
                $this->handleExistingOrder($payPalOrder->getIdCart());
            }

            if ($payPalOrder->getIdCart() !== $this->context->cart->id) {
                $logger->error('Cart Id mismatch for PayPal Order: ' . $this->paypalOrderId);
                $tools->redirect($this->orderPageUrl);
            }

            $payPalOrderResponse = $payPalOrderProvider->getById($this->paypalOrderId);
            $this->handleOrderStatus($payPalOrderResponse, $payPalOrder);
        } catch (Exception $exception) {
            $logger->error('Error processing PayPal Order: ' . $exception->getMessage());
            $this->context->smarty->assign('error', $exception->getMessage());
        }
    }

    /**
     * @param int $cartId
     *
     * @return void
     */
    private function handleExistingOrder(int $cartId)
    {
        /** @var Tools $tools */
        $tools = $this->module->getService(Tools::class);

        /** @var PayPalOrderProvider $payPalOrderProvider */
        $payPalOrderProvider = $this->module->getService(PayPalOrderProvider::class);

        if ($this->context->customer->isLogged()) {
            $tools->redirect($this->context->link->getPageLink('history'));
        } else {
            $payPalOrderResponse = $payPalOrderProvider->getById($this->paypalOrderId);

            $this->redirectToOrderConfirmationPage(
                $cartId,
                $payPalOrderResponse->getCapture()['id'] ?? null,
                $payPalOrderResponse->getStatus()
            );
        }
    }

    /**
     * @param PayPalOrderResponse $payPalOrderResponse
     * @param PayPalOrder $payPalOrder
     *
     * @return void
     *
     * @throws PayPalOrderException
     * @throws PsCheckoutException
     */
    private function handleOrderStatus(
        PayPalOrderResponse $payPalOrderResponse,
        PayPalOrder $payPalOrder
    ) {
        /** @var LoggerInterface $logger */
        $logger = $this->module->getService(LoggerInterface::class);

        /** @var CapturePayPalOrderAction $capturePayPalOrderAction */
        $capturePayPalOrderAction = $this->module->getService(CapturePayPalOrderAction::class);

        /** @var PayPalOrderProvider $payPalOrderProvider */
        $payPalOrderProvider = $this->module->getService(PayPalOrderProvider::class);

        switch ($payPalOrderResponse->getStatus()) {
            case PayPalOrderStatus::COMPLETED:
                $logger->info('PayPal Order Completed: ' . $this->paypalOrderId);
                $this->createOrder($payPalOrderResponse, $payPalOrder);

                break;
            case PayPalOrderStatus::PAYER_ACTION_REQUIRED:
                $logger->info('3DS Verification Required for PayPal Order: ' . $this->paypalOrderId);
                $this->redirectTo3DSVerification($payPalOrderResponse);

                break;
            case PayPalOrderStatus::CREATED:
                $this->handle3DSecureDecision($payPalOrderResponse, $payPalOrder);

                break;
            case PayPalOrderStatus::APPROVED:
                $logger->info('Capturing PayPal Order: ' . $this->paypalOrderId);
                $capturePayPalOrderAction->execute($payPalOrderResponse);
                $this->createOrder($payPalOrderProvider->getById($this->paypalOrderId), $payPalOrder);

                break;
        }
    }

    /**
     * @param PayPalOrderResponse $payPalOrderResponse
     * @param PayPalOrder $payPalOrder
     *
     * @return void
     *
     * @throws PsCheckoutException
     * @throws PayPalOrderException
     */
    private function handle3DSecureDecision(
        PayPalOrderResponse $payPalOrderResponse,
        PayPalOrder $payPalOrder
    ) {
        /** @var Card3DSecureValidator $card3DSecure */
        $card3DSecure = $this->module->getService(Card3DSecureValidator::class);

        /** @var CapturePayPalOrderAction $capturePayPalOrderAction */
        $capturePayPalOrderAction = $this->module->getService(CapturePayPalOrderAction::class);

        /** @var PayPalOrderProvider $payPalOrderProvider */
        $payPalOrderProvider = $this->module->getService(PayPalOrderProvider::class);

        /** @var PayPalConfiguration $payPalConfiguration */
        $payPalConfiguration = $this->module->getService(PayPalConfiguration::class);

        switch ($card3DSecure->getAuthorizationDecision($payPalOrderResponse)) {
            case Card3DSecureConfiguration::DECISION_RETRY:
                $this->redirectTo3DSVerification($payPalOrderResponse);

                break;
            case Card3DSecureConfiguration::DECISION_PROCEED:
                $capturePayPalOrderAction->execute($payPalOrderResponse);
                $this->createOrder($payPalOrderProvider->getById($this->paypalOrderId), $payPalOrder);

                break;
            case Card3DSecureConfiguration::DECISION_NO_DECISION:
                if ($payPalConfiguration->getCardFieldsContingencies() !== 'SCA_ALWAYS') {
                    $capturePayPalOrderAction->execute($payPalOrderResponse);
                    $this->createOrder($payPalOrderProvider->getById($this->paypalOrderId), $payPalOrder);
                }

                break;
        }
    }

    /**
     * @param PayPalOrderResponse $payPalOrderResponse
     * @param PayPalOrder $payPalOrder
     *
     * @return void
     *
     * @throws PsCheckoutException
     */
    private function createOrder(PayPalOrderResponse $payPalOrderResponse, PayPalOrder $payPalOrder)
    {
        /** @var CreateOrderAction $createOrderAction */
        $createOrderAction = $this->module->getService(CreateOrderAction::class);
        $createOrderAction->execute($payPalOrderResponse);

        if ($payPalOrder->getPaymentTokenId() && $payPalOrder->checkCustomerIntent(PayPalConfiguration::PS_CHECKOUT_CUSTOMER_INTENT_FAVORITE)) {
            /** @var PaymentTokenRepository $paymentTokenRepository */
            $paymentTokenRepository = $this->module->getService(PaymentTokenRepository::class);
            $paymentTokenRepository->setTokenFavorite($payPalOrder->getPaymentTokenId(), $payPalOrderResponse->getCustomerId());
        }

        $this->redirectToOrderConfirmationPage(
            $payPalOrder->getIdCart(),
            $payPalOrderResponse->getCapture()['id'] ?? null,
            $payPalOrderResponse->getStatus()
        );
    }

    /**
     * @param PayPalOrderResponse $paypalOrder
     *
     * @return void
     */
    private function redirectTo3DSVerification(PayPalOrderResponse $payPalOrderResponse)
    {
        /** @var Tools $tools */
        $tools = $this->module->getService(Tools::class);

        $payerActionLinks = array_filter($payPalOrderResponse->getLinks(), function ($link) {
            return $link['rel'] === 'payer-action';
        });

        if (!empty($payerActionLinks)) {
            $redirectUrl = reset($payerActionLinks)['href'] . '&redirect_uri=' . urlencode(
                $this->context->link->getModuleLink($this->module->name, 'payment', ['orderID' => $payPalOrderResponse->getId()])
            );

            /** @var LoggerInterface $logger */
            $logger = $this->module->getService(LoggerInterface::class);
            $logger->info('Redirecting to 3DS verification: ' . $redirectUrl);

            $tools->redirect($redirectUrl);
        }
    }

    /**
     * @param int $cartId
     * @param string|null $captureId
     * @param string $payPalOrderStatus
     *
     * @return void
     */
    private function redirectToOrderConfirmationPage(int $cartId, $captureId, string $payPalOrderStatus)
    {
        /** @var Tools $tools */
        $tools = $this->module->getService(Tools::class);
        /** @var OrderRepository $orderRepository */
        $orderRepository = $this->module->getService(OrderRepository::class);

        $orders = $orderRepository->getAllBy(['id_cart' => $cartId]);

        if (empty($orders)) {
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $orderConfirmationUrl = $this->context->link->getPageLink(
                'order-confirmation',
                true,
                $orders[0]->id_lang ?? null,
                [
                    'paypal_status' => $payPalOrderStatus,
                    'paypal_order' => $this->paypalOrderId,
                    'paypal_transaction' => $captureId,
                    'id_cart' => $cartId,
                    'id_module' => (int) $this->module->id,
                    'id_order' => $orders[0]->id ?? null,
                    'key' => (new Cart($cartId))->secure_key,
                ]
            );

            /** @var LoggerInterface $logger */
            $logger = $this->module->getService(LoggerInterface::class);
            $logger->info('Redirecting to order confirmation: ' . $orderConfirmationUrl);

            $tools->redirect($orderConfirmationUrl);
        }
    }
}

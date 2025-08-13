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

namespace PsCheckout\Core\Order\Processor;

use Cart;
use PsCheckout\Api\Http\Exception\PayPalException;
use PsCheckout\Core\Exception\PsCheckoutException;
use PsCheckout\Core\Order\Action\CreateOrderActionInterface;
use PsCheckout\Core\Order\Request\ValueObject\ValidateOrderRequest;
use PsCheckout\Core\Order\Validator\CheckoutValidatorInterface;
use PsCheckout\Core\Order\Validator\OrderAuthorizationValidatorInterface;
use PsCheckout\Core\PaymentToken\Action\DeletePaymentTokenActionInterface;
use PsCheckout\Core\PaymentToken\Action\SavePaymentTokenActionInterface;
use PsCheckout\Core\PayPal\Order\Action\CapturePayPalOrderActionInterface;
use PsCheckout\Core\PayPal\Order\Configuration\PayPalOrderStatus;
use PsCheckout\Core\PayPal\Order\Provider\PayPalOrderProviderInterface;
use PsCheckout\Core\PayPal\Order\Repository\PayPalOrderRepositoryInterface;
use PsCheckout\Infrastructure\Adapter\ContextInterface;
use PsCheckout\Infrastructure\Repository\CartRepositoryInterface;

class CreateOrderProcessor implements CreateOrderProcessorInterface
{
    /**
     * @var OrderAuthorizationValidatorInterface
     */
    private $orderAuthorizationValidator;

    /**
     * @var CreateOrderActionInterface
     */
    private $createOrderAction;

    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var ContextInterface
     */
    private $context;

    /**
     * @var CheckoutValidatorInterface
     */
    private $checkoutValidator;

    /**
     * @var CapturePayPalOrderActionInterface
     */
    private $capturePayPalOrderAction;

    /**
     * @var SavePaymentTokenActionInterface
     */
    private $savePaymentTokenAction;

    /**
     * @var PayPalOrderProviderInterface
     */
    private $payPalOrderProvider;

    /**
     * @var PayPalOrderRepositoryInterface
     */
    private $payPalOrderRepository;

    /**
     * @var DeletePaymentTokenActionInterface
     */
    private $deletePaymentTokenAction;

    public function __construct(
        OrderAuthorizationValidatorInterface $orderAuthorizationValidator,
        CreateOrderActionInterface $createOrderAction,
        CartRepositoryInterface $cartRepository,
        ContextInterface $context,
        CheckoutValidatorInterface $checkoutValidator,
        CapturePayPalOrderActionInterface $capturePayPalOrderAction,
        SavePaymentTokenActionInterface $savePaymentTokenAction,
        PayPalOrderProviderInterface $payPalOrderProvider,
        PayPalOrderRepositoryInterface $payPalOrderRepository,
        DeletePaymentTokenActionInterface $deletePaymentTokenAction
    ) {
        $this->orderAuthorizationValidator = $orderAuthorizationValidator;
        $this->createOrderAction = $createOrderAction;
        $this->cartRepository = $cartRepository;
        $this->context = $context;
        $this->checkoutValidator = $checkoutValidator;
        $this->capturePayPalOrderAction = $capturePayPalOrderAction;
        $this->savePaymentTokenAction = $savePaymentTokenAction;
        $this->payPalOrderProvider = $payPalOrderProvider;
        $this->payPalOrderRepository = $payPalOrderRepository;
        $this->deletePaymentTokenAction = $deletePaymentTokenAction;
    }

    /**
     * {@inheritDoc}
     */
    public function run(ValidateOrderRequest $request)
    {
        try {
            $this->checkoutValidator->validate($request->getOrderId(), $request->getCartId());
        } catch (PsCheckoutException $exception) {
            if ($exception->getCode() === PsCheckoutException::PRESTASHOP_ORDER_ALREADY_EXISTS) {
                return;
            }

            throw $exception;
        } catch (\Exception $exception) {
            throw new PsCheckoutException('Unknown error', PsCheckoutException::UNKNOWN);
        }

        /** @var Cart $cart */
        $cart = $this->cartRepository->getOneBy([
            'id_cart' => $request->getCartId(),
        ]);

        $this->context->setCurrentCart($cart);

        try {
            $payPalOrderResponse = $this->payPalOrderProvider->getById($request->getOrderId());

            $this->orderAuthorizationValidator->validate($cart->id, $payPalOrderResponse);
        } catch (PsCheckoutException $exception) {
            if ($exception->getCode() === PsCheckoutException::PAYPAL_ORDER_ALREADY_CAPTURED) {
                $this->createOrderAction->execute($payPalOrderResponse);

                return;
            }

            throw $exception;
        }

        try {
            $capturedOrderResponse = $this->capturePayPalOrderAction->execute($payPalOrderResponse);

            $this->savePaymentTokenAction->execute($capturedOrderResponse);
        } catch (PayPalException $exception) {
            switch ($exception->getCode()) {
                case PayPalException::ORDER_NOT_APPROVED:
                    $this->createOrderAction->execute($payPalOrderResponse);

                    return;

                case PayPalException::RESOURCE_NOT_FOUND:
                    $payPalOrder = $this->payPalOrderRepository->getOneBy(['id' => $request->getOrderId()]);

                    if ($payPalOrder) {
                        $payPalOrder->setStatus(PayPalOrderStatus::CANCELED);
                        $this->payPalOrderRepository->save($payPalOrder);
                    }

                    throw $exception;
                case PayPalException::ORDER_ALREADY_CAPTURED:
                    $capturedOrderResponse = $this->payPalOrderProvider->getById($request->getOrderId());
                    $this->createOrderAction->execute($capturedOrderResponse);

                    return;
                case PayPalException::CARD_CLOSED:
                    $capturedOrderResponse = $this->payPalOrderProvider->getById($request->getOrderId());
                    $this->deletePaymentTokenAction->execute(
                        $capturedOrderResponse->getVault()['id'],
                        $this->context->getCustomer()->id
                    );
                    // no break
                default:
                    throw $exception;
            }
        }
    }
}

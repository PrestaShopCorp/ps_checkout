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

use PsCheckout\Core\Exception\PsCheckoutException;
use PsCheckout\Core\Order\Exception\Handler\OrderCreationExceptionHandler;
use PsCheckout\Core\Order\Processor\CreateOrderProcessor;
use PsCheckout\Core\Order\Request\ValueObject\ValidateOrderRequest;
use PsCheckout\Core\PayPal\Order\Entity\PayPalOrder;
use PsCheckout\Core\PayPal\Order\Validator\CreatedPayPalOrderValidator;
use PsCheckout\Core\PayPal\Order\ValueObject\PayPalOrderCompletionData;
use PsCheckout\Infrastructure\Adapter\Tools;
use PsCheckout\Infrastructure\Adapter\Validate;
use PsCheckout\Infrastructure\Controller\AbstractFrontController;
use PsCheckout\Infrastructure\Repository\PayPalOrderRepository;
use PsCheckout\Utility\Common\InputStreamUtility;

/**
 * This controller receive ajax call to capture/authorize payment and create a PrestaShop Order
 */
class Ps_CheckoutValidateModuleFrontController extends AbstractFrontController
{
    /**
     * @var Ps_checkout
     */
    public $module;

    /**
     * @see FrontController::postProcess()
     */
    public function postProcess()
    {
        /** @var Tools $tools */
        $tools = $this->module->getService(Tools::class);

        /** @var Validate $validate */
        $validate = $this->module->getService(Validate::class);

        /** @var CreatedPayPalOrderValidator $createdPayPalOrderValidator */
        $createdPayPalOrderValidator = $this->module->getService(CreatedPayPalOrderValidator::class);

        $checkoutRequest = null;

        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                /** @var InputStreamUtility $inputStreamUtility */
                $inputStreamUtility = $this->module->getService(InputStreamUtility::class);
                $bodyContent = $inputStreamUtility->getBodyContent();

                if (empty($bodyContent)) {
                    $this->exitWithResponse([
                        'httpCode' => 400,
                        'body' => 'Payload invalid',
                    ]);
                }

                $bodyValues = json_decode($bodyContent, true);
            } else {
                $bodyValues = [
                    'orderID' => $tools->getValue('token'),
                    'payerID' => $tools->getValue('token')('PayerID'),
                ];
            }

            /** @var PayPalOrderRepository $payPalOrderRepository */
            $payPalOrderRepository = $this->module->getService(PayPalOrderRepository::class);

            /** @var PayPalOrder $payPalOrder */
            $payPalOrder = $payPalOrderRepository->getOneBy(['id' => $bodyValues['orderID']]);

            if (!$payPalOrder) {
                throw new PsCheckoutException('PayPal Order not found', PsCheckoutException::PAYPAL_ORDER_NOT_FOUND);
            }

            $checkoutRequest = new ValidateOrderRequest($bodyValues, $payPalOrder->getIdCart());

            if (empty($checkoutRequest->getOrderId()) || !$validate->isGenericName($checkoutRequest->getOrderId())) {
                $this->exitWithResponse([
                    'httpCode' => 400,
                    'body' => 'Payload invalid',
                ]);
            }

            /** @var CreateOrderProcessor $createOrderProcessor */
            $createOrderProcessor = $this->module->getService(CreateOrderProcessor::class);
            $createOrderProcessor->run($checkoutRequest);

            $completedPayPalOrderData = $createdPayPalOrderValidator->validate($checkoutRequest->getOrderId(), $checkoutRequest->getCartId());

            if (empty($completedPayPalOrderData)) {
                throw new PsCheckoutException('PayPal Order not found', PsCheckoutException::PAYPAL_ORDER_NOT_FOUND);
            }

            $this->sendOkResponse($completedPayPalOrderData);
        } catch (Exception $exception) {
            if (!$checkoutRequest) {
                $this->handleOrderCreationException($exception, 'unknown');
            }

            // NOTE: Retry to get the PayPal Order after the order creation
            $completedPayPalOrderData = $createdPayPalOrderValidator->validate($checkoutRequest->getOrderId(), $checkoutRequest->getCartId());

            if (!empty($completedPayPalOrderData)) {
                $this->sendOkResponse($completedPayPalOrderData);
            }

            $this->handleOrderCreationException($exception, $checkoutRequest->getOrderId());
        }
    }

    /**
     * @param PayPalOrderCompletionData $orderCompletionData
     *
     * @return void
     */
    private function sendOkResponse(PayPalOrderCompletionData $orderCompletionData)
    {
        $this->exitWithResponse([
            'status' => true,
            'httpCode' => 200,
            'body' => $orderCompletionData->toArray(),
            'exceptionCode' => null,
            'exceptionMessage' => null,
        ]);
    }

    /**
     * @param Exception $exception
     * @param string $paypalOrderId
     *
     * @return void
     */
    public function handleOrderCreationException(Exception $exception, string $paypalOrderId)
    {
        /** @var OrderCreationExceptionHandler $orderCreationExceptionHandler */
        $orderCreationExceptionHandler = $this->module->getService(OrderCreationExceptionHandler::class);
        $responseData = $orderCreationExceptionHandler->handle($exception, $paypalOrderId);

        if ($responseData['httpCode'] === 500) {
            $this->exitWithResponse($responseData);
        } else {
            $this->sendBadRequestError($responseData['body']['error']['message'], $exception);
        }
    }

    /**
     * @param string $exceptionMessageForCustomer
     * @param Exception $exception
     *
     * @return void
     */
    private function sendBadRequestError(string $exceptionMessageForCustomer, Exception $exception)
    {
        $this->exitWithResponse([
            'status' => false,
            'httpCode' => 400,
            'body' => [
                'error' => [
                    'code' => $exception->getCode(),
                    'message' => $exceptionMessageForCustomer,
                ],
            ],
        ]);
    }
}

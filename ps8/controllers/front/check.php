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
use PsCheckout\Core\Order\Validator\CartOwnershipValidator;
use PsCheckout\Core\PayPal\Order\Action\CancelPayPalOrderAction;
use PsCheckout\Core\PayPal\Order\Processor\UpdateExternalPayPalOrderProcessor;
use PsCheckout\Core\PayPal\Order\Request\ValueObject\CancelPayPalOrderRequest;
use PsCheckout\Core\PayPal\Order\Request\ValueObject\CheckPayPalOrderRequest;
use PsCheckout\Infrastructure\Controller\AbstractFrontController;
use PsCheckout\Infrastructure\Repository\PayPalOrderRepository;
use PsCheckout\Utility\Common\InputStreamUtility;
use Psr\Log\LoggerInterface;

/**
 * This controller receive ajax call on customer click on a payment button
 */
class Ps_CheckoutCheckModuleFrontController extends AbstractFrontController
{
    /**
     * @see FrontController::postProcess()
     */
    public function postProcess()
    {
        /** @var LoggerInterface $logger */
        $logger = $this->module->getService(LoggerInterface::class);

        try {
            if (!Validate::isLoadedObject($this->context->cart)) {
                $this->exitWithResponse([
                    'httpCode' => 400,
                    'body' => 'No cart found.',
                ]);
            }

            /** @var InputStreamUtility $inputStreamUtility */
            $inputStreamUtility = $this->module->getService(InputStreamUtility::class);
            $bodyContent = $inputStreamUtility->getBodyContent();

            $bodyValues = json_decode($bodyContent, true);

            if (empty($bodyContent) || empty($bodyValues)) {
                $this->exitWithResponse([
                    'httpCode' => 400,
                    'body' => 'Payload invalid',
                ]);
            }

            $checkOrderRequest = new CheckPayPalOrderRequest($this->context->cart->id, $bodyValues);

            if (!$checkOrderRequest->getOrderId()) {
                $this->exitWithResponse([
                    'httpCode' => 400,
                    'body' => 'Missing PayPal Order Id',
                ]);
            }

            $this->assertPayPalOrderBelongsToCurrentCustomer($checkOrderRequest->getOrderId());

            try {
                /** @var UpdateExternalPayPalOrderProcessor $updateExternalPayPalOrderProcessor */
                $updateExternalPayPalOrderProcessor = $this->module->getService(UpdateExternalPayPalOrderProcessor::class);

                $updateExternalPayPalOrderProcessor->execute($checkOrderRequest);
            } catch (Exception $exception) {
                $logger->error(
                    'Failed to patch PayPal Order',
                    [
                        'PayPalOrderId' => $checkOrderRequest->getOrderId(),
                        'FundingSource' => $checkOrderRequest->getFundingSource(),
                        'isExpressCheckout' => $checkOrderRequest->isExpressCheckout(),
                        'isHostedFields' => $checkOrderRequest->isHostedFields(),
                        'id_cart' => (int) $this->context->cart->id,
                    ]
                );

                /** @var CancelPayPalOrderAction $cancelPayPalOrderAction */
                $cancelPayPalOrderAction = $this->module->getService(CancelPayPalOrderAction::class);
                $cancelPayPalOrderAction->execute(new CancelPayPalOrderRequest($bodyValues, $this->context->cart->id));
            }

            $this->exitWithResponse([
                'status' => true,
                'httpCode' => 200,
                'body' => $bodyValues,
                'exceptionCode' => null,
                'exceptionMessage' => null,
            ]);
        } catch (Exception $exception) {
            $logger->error(
                sprintf(
                    'CheckController - Exception %s : %s',
                    $exception->getCode(),
                    $exception->getMessage()
                )
            );

            $this->exitWithExceptionMessage($exception);
        } catch (Throwable $exception) {
            $this->exitWithExceptionMessage(new PsCheckoutException(
                'An error occurred while checking the PayPal order.',
                PsCheckoutException::UNKNOWN,
                $exception
            ));
        }
    }

    /**
     * The PayPal Order must exist and belong to the current visitor, otherwise anyone holding
     * a PayPal Order id could rewrite that order from their own cart. Unknown and foreign orders
     * get the same answer, so that the response does not tell whether the order exists.
     *
     * Unlike validate and payment, this controller is reachable before a customer exists: an
     * anonymous visitor starting an express checkout from the product or cart page owns a
     * PayPal Order linked to a cart without customer, so the order is also accepted when it is
     * linked to the cart of the current context (the visitor's signed cookie).
     *
     * @param string $payPalOrderId
     *
     * @return void
     */
    private function assertPayPalOrderBelongsToCurrentCustomer($payPalOrderId)
    {
        /** @var PayPalOrderRepository $payPalOrderRepository */
        $payPalOrderRepository = $this->module->getService(PayPalOrderRepository::class);

        /** @var CartOwnershipValidator $cartOwnershipValidator */
        $cartOwnershipValidator = $this->module->getService(CartOwnershipValidator::class);

        $payPalOrder = $payPalOrderRepository->getOneBy(['id' => $payPalOrderId]);

        if ($payPalOrder && $cartOwnershipValidator->isOwnedByCurrentVisitor($payPalOrder->getIdCart())) {
            return;
        }

        /** @var LoggerInterface $logger */
        $logger = $this->module->getService(LoggerInterface::class);
        $logger->warning(
            $payPalOrder ? 'PayPal Order does not belong to the current visitor' : 'PayPal Order not found',
            [
                'PayPalOrderId' => $payPalOrderId,
                'id_cart' => (int) $this->context->cart->id,
                'PayPalOrderCartId' => $payPalOrder ? $payPalOrder->getIdCart() : null,
            ]
        );

        $this->exitWithResponse([
            'status' => false,
            'httpCode' => 404,
            'body' => [
                'error' => [
                    'code' => PsCheckoutException::PAYPAL_ORDER_NOT_FOUND,
                    'message' => 'PayPal Order not found',
                ],
            ],
        ]);
    }
}

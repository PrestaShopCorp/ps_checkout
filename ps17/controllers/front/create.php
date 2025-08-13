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
use PsCheckout\Core\PayPal\Order\Action\CreatePayPalOrderAction;
use PsCheckout\Core\PayPal\Order\Entity\PayPalOrder;
use PsCheckout\Core\PayPal\Order\Request\ValueObject\CreatePayPalOrderRequest;
use PsCheckout\Core\PayPal\OrderStatus\Configuration\PayPalOrderStatusConfiguration;
use PsCheckout\Infrastructure\Action\AddProductToCartAction;
use PsCheckout\Infrastructure\Adapter\Context;
use PsCheckout\Infrastructure\Controller\AbstractFrontController;
use PsCheckout\Infrastructure\Repository\PayPalOrderRepository;
use PsCheckout\Utility\Common\InputStreamUtility;
use Psr\Log\LoggerInterface;

/**
 * This controller receives ajax call to create a PayPal Order
 */
class Ps_CheckoutCreateModuleFrontController extends AbstractFrontController
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
        try {
            /** @var Context $context */
            $context = $this->module->getService(Context::class);

            // BEGIN Express Checkout
            $requestData = [];

            /** @var InputStreamUtility $inputStreamUtility */
            $inputStreamUtility = $this->module->getService(InputStreamUtility::class);
            $bodyContent = $inputStreamUtility->getBodyContent();

            if (!empty($bodyContent)) {
                $requestData = json_decode($bodyContent, true);
            }

            $requestData['isExpressCheckout'] = (isset($requestData['isExpressCheckout']) && $requestData['isExpressCheckout']) || empty($this->context->cart->id_address_delivery);

            $createPayPalOrderRequest = new CreatePayPalOrderRequest($requestData);

            if ($this->shouldCreateCart($createPayPalOrderRequest)) {
                try {
                    /** @var AddProductToCartAction $addProductToCartAction */
                    $addProductToCartAction = $this->module->getService(AddProductToCartAction::class);
                    $addProductToCartAction->execute($createPayPalOrderRequest);
                } catch (PsCheckoutException $exception) {
                    $this->exitWithResponse([
                        'status' => false,
                        'httpCode' => 400,
                        'body' => [
                            'error' => [
                                'message' => 'Failed to update cart quantity.',
                            ],
                        ],
                    ]);
                }
            }
            // END Express Checkout

            if (!isset($context->getCart()->id)) {
                $this->exitWithResponse([
                    'httpCode' => 404,
                    'body' => 'Cart not found',
                ]);
            }

            if ($createPayPalOrderRequest->isExpressCheckout() || empty($context->getCart()->id_address_delivery)) {
                /** @var PayPalOrderRepository $payPalOrderRepository */
                $payPalOrderRepository = $this->module->getService(PayPalOrderRepository::class);

                /** @var PayPalOrder|null $payPalOrder */
                $payPalOrder = $payPalOrderRepository->getOneBy(
                    [
                        'id_cart' => (int) $context->getCart()->id,
                        'is_express_checkout' => '1',
                    ]
                );

                if ($payPalOrder && in_array(
                    $payPalOrder->getStatus(),
                    [
                            PayPalOrderStatusConfiguration::STATUS_CREATED,
                            PayPalOrderStatusConfiguration::STATUS_APPROVED,
                            PayPalOrderStatusConfiguration::STATUS_PAYER_ACTION_REQUIRED,
                        ],
                    true
                )) {
                    $this->exitWithResponse([
                        'status' => true,
                        'httpCode' => 200,
                        'body' => [
                            'orderID' => $payPalOrder->getId(),
                        ],
                        'exceptionCode' => null,
                        'exceptionMessage' => null,
                    ]);
                }
            }

            /** @var CreatePayPalOrderAction $createPayPalOrderAction */
            $createPayPalOrderAction = $this->module->getService(CreatePayPalOrderAction::class);
            $createPayPalOrderAction->execute((int) $context->getCart()->id, $createPayPalOrderRequest);

            /** @var PayPalOrderRepository $payPalOrderRepository */
            $payPalOrderRepository = $this->module->getService(PayPalOrderRepository::class);
            $payPalOrder = $payPalOrderRepository->getOneByCartId((int) $context->getCart()->id);

            if (!$payPalOrder) {
                $this->exitWithResponse([
                    'httpCode' => 404,
                    'body' => 'PayPal order not found',
                ]);
            }

            $this->exitWithResponse([
                'status' => true,
                'httpCode' => 200,
                'body' => [
                    'orderID' => $payPalOrder->getId(),
                ],
                'exceptionCode' => null,
                'exceptionMessage' => null,
            ]);
        } catch (Exception $exception) {
            $this->module->getService(LoggerInterface::class)->error(
                'CreateController - Exception ' . $exception->getCode(),
                [
                    'exception' => $exception,
                ]
            );

            $this->exitWithExceptionMessage(new PsCheckoutException('Unexpected error ocurred.', $exception->getCode()));
        }
    }

    /**
     * @param CreatePayPalOrderRequest $checkoutRequest
     *
     * @return bool
     */
    private function shouldCreateCart(CreatePayPalOrderRequest $checkoutRequest): bool
    {
        return $checkoutRequest->getQuantityWanted() !== null
            || $checkoutRequest->getIdProduct() !== null
            || $checkoutRequest->getIdProductAttribute() !== null
            || $checkoutRequest->getIdCustomization() !== null;
    }
}

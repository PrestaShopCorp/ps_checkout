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

use PrestaShop\Module\PrestashopCheckout\Controller\AbstractFrontController;
use PrestaShop\Module\PrestashopCheckout\Exception\PsCheckoutException;
use PrestaShop\Module\PrestashopCheckout\Handler\CreatePaypalOrderHandler;
use PrestaShop\Module\PrestashopCheckout\Repository\PsCheckoutCartRepository;

/**
 * This controller receive ajax call on customer click on a payment button
 */
class Ps_CheckoutCheckModuleFrontController extends AbstractFrontController
{
    /**
     * @var Ps_checkout
     */
    public $module;

    /**
     * @see FrontController::postProcess()
     *
     * @todo Move logic to a Service
     */
    public function postProcess()
    {
        try {
            if (false === Validate::isLoadedObject($this->context->cart)) {
                $this->exitWithResponse([
                    'httpCode' => 400,
                    'body' => 'No cart found.',
                ]);
            }

            $bodyContent = file_get_contents('php://input');

            if (empty($bodyContent)) {
                $this->exitWithResponse([
                    'httpCode' => 400,
                    'body' => 'Payload invalid',
                ]);
            }

            $bodyValues = json_decode($bodyContent, true);

            if (empty($bodyValues)) {
                $this->exitWithResponse([
                    'httpCode' => 400,
                    'body' => 'Payload invalid',
                ]);
            }

            $fundingSource = isset($bodyValues['fundingSource']) ? $bodyValues['fundingSource'] : 'paypal';
            $orderId = isset($bodyValues['orderID']) ? $bodyValues['orderID'] : null;
            $isExpressCheckout = isset($bodyValues['isExpressCheckout']) && $bodyValues['isExpressCheckout'];
            $isHostedFields = isset($bodyValues['isHostedFields']) && $bodyValues['isHostedFields'];

            if (empty($orderId)) {
                $this->exitWithResponse([
                    'httpCode' => 400,
                    'body' => 'Missing PayPal Order Id',
                ]);
            }

            /** @var PsCheckoutCartRepository $psCheckoutCartRepository */
            $psCheckoutCartRepository = $this->module->getService('ps_checkout.repository.pscheckoutcart');

            /** @var PsCheckoutCart|false $psCheckoutCart */
            $psCheckoutCart = $psCheckoutCartRepository->findOneByPayPalOrderId($orderId);

            if (false === $psCheckoutCart) {
                $psCheckoutCart = new PsCheckoutCart();
                $psCheckoutCart->id_cart = (int) $this->context->cart->id;
            }

            if ($fundingSource) {
                $psCheckoutCart->paypal_funding = $fundingSource;
            }

            $psCheckoutCart->isExpressCheckout = $isExpressCheckout;
            $psCheckoutCart->isHostedFields = $isHostedFields;
            $psCheckoutCartRepository->save($psCheckoutCart);

            if (false === empty($psCheckoutCart->paypal_order)) {
                $paypalOrder = new CreatePaypalOrderHandler($this->context);
                $response = $paypalOrder->handle($isExpressCheckout || empty($this->context->cart->id_address_delivery), true, $psCheckoutCart->paypal_order);

                if (false === $response['status']) {
                    $this->module->getLogger()->error(
                        'Failed to patch PayPal Order',
                        [
                            'PayPalOrderId' => $orderId,
                            'FundingSource' => $fundingSource,
                            'isExpressCheckout' => $isExpressCheckout,
                            'isHostedFields' => $isHostedFields,
                            'id_cart' => (int) $this->context->cart->id,
                            'response' => $response,
                        ]
                    );
                    $psCheckoutCart->paypal_status = PsCheckoutCart::STATUS_CANCELED;
                    $psCheckoutCartRepository->save($psCheckoutCart);
                    throw new PsCheckoutException(sprintf('Unable to patch PayPal Order - Exception %s : %s', $response['exceptionCode'], $response['exceptionMessage']), PsCheckoutException::PSCHECKOUT_UPDATE_ORDER_HANDLE_ERROR);
                }

                $this->module->getLogger()->info(
                    'PayPal Order patched',
                    [
                        'PayPalOrderId' => $orderId,
                        'FundingSource' => $fundingSource,
                        'isExpressCheckout' => $isExpressCheckout,
                        'isHostedFields' => $isHostedFields,
                        'id_cart' => (int) $this->context->cart->id,
                    ]
                );
            }

            $this->exitWithResponse([
                'status' => true,
                'httpCode' => 200,
                'body' => $bodyValues,
                'exceptionCode' => null,
                'exceptionMessage' => null,
            ]);
        } catch (Exception $exception) {
            $this->module->getLogger()->error(
                sprintf(
                    'CheckController - Exception %s : %s',
                    $exception->getCode(),
                    $exception->getMessage()
                )
            );

            $this->exitWithExceptionMessage($exception);
        }
    }
}

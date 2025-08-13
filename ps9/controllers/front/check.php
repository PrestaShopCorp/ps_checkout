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

use PsCheckout\Core\PayPal\Order\Action\CancelPayPalOrderAction;
use PsCheckout\Core\PayPal\Order\Processor\UpdateExternalPayPalOrderProcessor;
use PsCheckout\Core\PayPal\Order\Request\ValueObject\CancelPayPalOrderRequest;
use PsCheckout\Core\PayPal\Order\Request\ValueObject\CheckPayPalOrderRequest;
use PsCheckout\Infrastructure\Controller\AbstractFrontController;
use PsCheckout\Utility\Common\InputStreamUtility;
use Psr\Log\LoggerInterface;

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
        }
    }
}

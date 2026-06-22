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
use PsCheckout\Core\PayPal\Order\Action\CancelPayPalOrderAction;
use PsCheckout\Core\PayPal\Order\Request\ValueObject\CancelPayPalOrderRequest;
use PsCheckout\Infrastructure\Action\ShippingCallbackProcessor;
use PsCheckout\Infrastructure\Controller\AbstractFrontController;
use PsCheckout\Infrastructure\Repository\AddressRepository;
use PsCheckout\Utility\Common\InputStreamUtility;
use Psr\Log\LoggerInterface;

/**
 * This controller receive ajax call on customer canceled payment
 */
class Ps_CheckoutCancelModuleFrontController extends AbstractFrontController
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

            $orderCancelRequest = new CancelPayPalOrderRequest($bodyValues, $this->context->cart->id);

            if ($orderCancelRequest->getOrderId()) {
                /** @var CancelPayPalOrderAction $cancelPayPalOrderAction */
                $cancelPayPalOrderAction = $this->module->getService(CancelPayPalOrderAction::class);
                $cancelPayPalOrderAction->execute($orderCancelRequest);
            }

            /** @var AddressRepository $addressRepository */
            $addressRepository = $this->module->getService(AddressRepository::class);
            $alias = ShippingCallbackProcessor::TEMPORARY_ADDRESS_ALIAS_PREFIX . $this->context->cart->id;
            $customerId = (int) $this->context->cart->id_customer;
            $tempAddressId = $addressRepository->getAddressIdByAliasAndCustomer($alias, $customerId);
            $originalAddressId = 0;
            if ($tempAddressId > 0) {
                $tempAddress = new Address($tempAddressId);
                $originalAddressId = (int) $tempAddress->other;
            }
            $addressRepository->deleteByAliasAndCustomer($alias, $customerId);
            if ($tempAddressId > 0 && (int) $this->context->cart->id_address_delivery === $tempAddressId) {
                $this->context->cart->id_address_delivery = $originalAddressId;
                $this->context->cart->save();
            }

            $logger->log(
                $orderCancelRequest->getError() ? 400 : 200,
                'Customer canceled payment',
                [
                    'PayPalOrderId' => $orderCancelRequest->getOrderId(),
                    'FundingSource' => $orderCancelRequest->getFundingSource(),
                    'id_cart' => $this->context->cart->id,
                    'isExpressCheckout' => $orderCancelRequest->isExpressCheckout(),
                    'isHostedFields' => $orderCancelRequest->isHostedFields(),
                    'reason' => $orderCancelRequest->getReason(),
                    'error' => $orderCancelRequest->getError(),
                ]
            );

            $this->exitWithResponse([
                'status' => true,
                'httpCode' => 200,
                'body' => $bodyValues,
                'exceptionCode' => null,
                'exceptionMessage' => null,
            ]);
        } catch (\Exception $exception) {
            $logger->error(
                'CancelController - Exception ' . $exception->getCode(),
                [
                    'exception' => $exception,
                ]
            );

            $this->exitWithExceptionMessage($exception);
        } catch (Throwable $exception) {
            $this->exitWithExceptionMessage(new PsCheckoutException(
                'An error occurred while canceling the PayPal order.',
                PsCheckoutException::UNKNOWN,
                $exception
            ));
        }
    }
}

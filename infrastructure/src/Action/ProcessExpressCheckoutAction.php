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

namespace PsCheckout\Infrastructure\Action;

use Exception;
use PsCheckout\Api\Http\OrderHttpClient;
use PsCheckout\Api\ValueObject\PayPalOrderResponse;
use PsCheckout\Core\Customer\Action\ExpressCheckoutActionInterface;
use PsCheckout\Core\Customer\Request\ValueObject\ExpressCheckoutPayerData;
use PsCheckout\Core\Customer\Request\ValueObject\ExpressCheckoutShippingData;
use PsCheckout\Core\Exception\PsCheckoutException;
use PsCheckout\Core\PayPal\Order\Repository\PayPalOrderRepositoryInterface;
use PsCheckout\Infrastructure\Adapter\ContextInterface;
use PsCheckout\Infrastructure\Validator\FrontControllerValidator;
use PsCheckout\Utility\Common\InputStreamUtility;
use Psr\Log\LoggerInterface;

class ProcessExpressCheckoutAction
{
    /** @var FrontControllerValidator */
    private $frontControllerValidator;

    /** @var InputStreamUtility */
    private $inputStreamUtility;

    /** @var PayPalOrderRepositoryInterface */
    private $payPalOrderRepository;

    /** @var OrderHttpClient */
    private $orderHttpClient;

    /** @var SaveExpressCheckoutFlagsAction */
    private $saveExpressCheckoutFlagsAction;

    /** @var ExpressCheckoutActionInterface */
    private $expressCheckoutAction;

    /** @var ContextInterface */
    private $context;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        FrontControllerValidator $frontControllerValidator,
        InputStreamUtility $inputStreamUtility,
        PayPalOrderRepositoryInterface $payPalOrderRepository,
        OrderHttpClient $orderHttpClient,
        SaveExpressCheckoutFlagsAction $saveExpressCheckoutFlagsAction,
        ExpressCheckoutActionInterface $expressCheckoutAction,
        ContextInterface $context,
        LoggerInterface $logger
    ) {
        $this->frontControllerValidator = $frontControllerValidator;
        $this->inputStreamUtility = $inputStreamUtility;
        $this->payPalOrderRepository = $payPalOrderRepository;
        $this->orderHttpClient = $orderHttpClient;
        $this->saveExpressCheckoutFlagsAction = $saveExpressCheckoutFlagsAction;
        $this->expressCheckoutAction = $expressCheckoutAction;
        $this->context = $context;
        $this->logger = $logger;
    }

    /**
     * @return array{orderID: string, fundingSource: string|null}
     *
     * @throws PsCheckoutException
     * @throws Exception
     */
    public function execute()
    {
        if (!$this->frontControllerValidator->isExpressCheckoutEnabled()) {
            throw new PsCheckoutException(
                'Express checkout is not enabled.',
                PsCheckoutException::PSCHECKOUT_EXPRESS_CHECKOUT_NOT_ENABLED
            );
        }

        $bodyContent = $this->inputStreamUtility->getBodyContent();
        if (empty($bodyContent)) {
            throw new PsCheckoutException(
                'Payload invalid',
                PsCheckoutException::PSCHECKOUT_EXPRESS_CHECKOUT_INVALID_PAYLOAD
            );
        }

        $requestData = json_decode($bodyContent, true);
        if (empty($requestData)) {
            throw new PsCheckoutException(
                'Payload invalid',
                PsCheckoutException::PSCHECKOUT_EXPRESS_CHECKOUT_INVALID_PAYLOAD
            );
        }

        $orderID = $requestData['orderID'] ?? null;
        $fundingSource = $requestData['fundingSource'] ?? null;

        if (!$orderID) {
            throw new PsCheckoutException(
                'Payload invalid',
                PsCheckoutException::PSCHECKOUT_EXPRESS_CHECKOUT_INVALID_PAYLOAD
            );
        }

        $payPalOrder = $this->payPalOrderRepository->getOneBy(['id' => $orderID]);
        $cart = $this->context->getCart();

        if (!$payPalOrder || null === $cart || $payPalOrder->getIdCart() !== $cart->id) {
            throw new PsCheckoutException(
                'Payload invalid',
                PsCheckoutException::PSCHECKOUT_EXPRESS_CHECKOUT_INVALID_PAYLOAD
            );
        }

        try {
            $responseBody = json_decode(
                (string) $this->orderHttpClient->fetchOrder($orderID)->getBody(),
                true
            );
            $orderResponse = new PayPalOrderResponse(
                $responseBody['id'],
                $responseBody['status'],
                $responseBody['intent'],
                $responseBody['payer'] ?? null,
                $responseBody['payment_source'] ?? null,
                $responseBody['purchase_units'],
                $responseBody['links']
            );

            $wallet = $orderResponse->getPayPalWallet() ?? [];
            $purchaseUnits = $orderResponse->getPurchaseUnits();
            $shippingAddress = $purchaseUnits[0]['shipping']['address'] ?? null;
            if (empty($shippingAddress)) {
                $shippingAddress = $wallet['address'] ?? [];
            }

            $payerData = new ExpressCheckoutPayerData(
                $orderID,
                $wallet['email_address'] ?? null,
                $wallet['name']['given_name'] ?? null,
                $wallet['name']['surname'] ?? null,
                $wallet['phone_number']['national_number'] ?? null,
                $wallet['birth_date'] ?? null
            );

            $shippingData = new ExpressCheckoutShippingData(
                $orderID,
                $wallet['name']['given_name'] ?? null,
                $wallet['name']['surname'] ?? null,
                $shippingAddress['address_line_1'] ?? null,
                $shippingAddress['address_line_2'] ?? null,
                $shippingAddress['postal_code'] ?? null,
                $shippingAddress['admin_area_2'] ?? null,
                $shippingAddress['admin_area_1'] ?? null,
                $shippingAddress['country_code'] ?? null,
                $wallet['phone_number']['national_number'] ?? null
            );

            $this->saveExpressCheckoutFlagsAction->execute($orderID, $fundingSource);
            $this->expressCheckoutAction->execute($payerData, $shippingData);
        } catch (Exception $exception) {
            $this->logger->error(
                sprintf(
                    'ProcessExpressCheckoutAction - Exception %s : %s',
                    $exception->getCode(),
                    $exception->getMessage()
                ),
                [
                    'paypal_order' => $orderID,
                    'exception' => $exception,
                ]
            );

            throw $exception;
        }

        return [
            'orderID' => $orderID,
            'fundingSource' => $fundingSource,
        ];
    }
}

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
use PsCheckout\Api\Http\OrderHttpClientInterface;
use PsCheckout\Api\ValueObject\PayPalOrderResponse;
use PsCheckout\Core\Customer\Action\ExpressCheckoutActionInterface;
use PsCheckout\Core\Customer\Request\ValueObject\ExpressCheckoutPayerData;
use PsCheckout\Core\Customer\Request\ValueObject\ExpressCheckoutShippingData;
use PsCheckout\Core\Exception\PsCheckoutException;
use PsCheckout\Core\PayPal\Order\Repository\PayPalOrderRepositoryInterface;
use PsCheckout\Infrastructure\Adapter\ContextInterface;
use PsCheckout\Infrastructure\Repository\AddressRepositoryInterface;
use PsCheckout\Infrastructure\Validator\FrontControllerValidatorInterface;
use PsCheckout\Utility\Common\InputStreamUtility;
use Psr\Log\LoggerInterface;

class ProcessExpressCheckoutAction
{
    /** @var FrontControllerValidatorInterface */
    private $frontControllerValidator;

    /** @var InputStreamUtility */
    private $inputStreamUtility;

    /** @var PayPalOrderRepositoryInterface */
    private $payPalOrderRepository;

    /** @var OrderHttpClientInterface */
    private $orderHttpClient;

    /** @var SaveExpressCheckoutFlagsAction */
    private $saveExpressCheckoutFlagsAction;

    /** @var ExpressCheckoutActionInterface */
    private $expressCheckoutAction;

    /** @var ContextInterface */
    private $context;

    /** @var AddressRepositoryInterface */
    private $addressRepository;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        FrontControllerValidatorInterface $frontControllerValidator,
        InputStreamUtility $inputStreamUtility,
        PayPalOrderRepositoryInterface $payPalOrderRepository,
        OrderHttpClientInterface $orderHttpClient,
        SaveExpressCheckoutFlagsAction $saveExpressCheckoutFlagsAction,
        ExpressCheckoutActionInterface $expressCheckoutAction,
        ContextInterface $context,
        AddressRepositoryInterface $addressRepository,
        LoggerInterface $logger
    ) {
        $this->frontControllerValidator = $frontControllerValidator;
        $this->inputStreamUtility = $inputStreamUtility;
        $this->payPalOrderRepository = $payPalOrderRepository;
        $this->orderHttpClient = $orderHttpClient;
        $this->saveExpressCheckoutFlagsAction = $saveExpressCheckoutFlagsAction;
        $this->expressCheckoutAction = $expressCheckoutAction;
        $this->context = $context;
        $this->addressRepository = $addressRepository;
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

        // Capture customer ID before authentication: for guest checkouts, ExpressCheckoutAction
        // calls context->updateCustomer() which updates $cart->id_customer from 0 to the new
        // guest customer ID. The temp address was saved with the original id_customer (0 for guests),
        // so we must use the pre-auth value to find and delete it in the finally block.
        $customerIdBeforeAuth = (int) $cart->id_customer;

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

            $shippingPhone = $purchaseUnits[0]['shipping']['phone_number']['national_number']
                ?? $wallet['phone_number']['national_number']
                ?? null;

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
                $shippingPhone
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
        } finally {
            $alias = ShippingCallbackProcessor::TEMPORARY_ADDRESS_ALIAS_PREFIX . $cart->id;
            $tempAddressId = $this->addressRepository->getAddressIdByAliasAndCustomer($alias, $customerIdBeforeAuth);
            $this->addressRepository->deleteByAliasAndCustomer($alias, $customerIdBeforeAuth);

            if ($tempAddressId > 0) {
                $this->logger->info('ProcessExpressCheckoutAction: temporary delivery address deleted', [
                    'id_cart' => (int) $cart->id,
                    'id_address_temp' => $tempAddressId,
                    'id_address_delivery_after' => (int) $cart->id_address_delivery,
                    'delivery_option' => (string) $cart->delivery_option,
                ]);
            }

            // If the cart still points to the now-deleted temp address, reset it to avoid a dangling pointer.
            // On the success path, expressCheckoutAction already updated id_address_delivery to the real address.
            if ($tempAddressId > 0 && (int) $cart->id_address_delivery === $tempAddressId) {
                $cart->id_address_delivery = 0;
                $cart->save();
                $this->logger->warning('ProcessExpressCheckoutAction: cart still pointed to deleted temp address, reset to 0', [
                    'id_cart' => (int) $cart->id,
                    'id_address_temp' => $tempAddressId,
                ]);
            }
        }

        return [
            'orderID' => $orderID,
            'fundingSource' => $fundingSource,
        ];
    }
}

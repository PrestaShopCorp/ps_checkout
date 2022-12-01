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

use PrestaShop\Module\PrestashopCheckout\Adapter\CountryAdapter;
use PrestaShop\Module\PrestashopCheckout\Builder\Address\CheckoutAddress;
use PrestaShop\Module\PrestashopCheckout\Builder\Address\PaypalAddressBuilder;
use PrestaShop\Module\PrestashopCheckout\Controller\AbstractFrontController;
use PrestaShop\Module\PrestashopCheckout\Exception\PsCheckoutException;
use PrestaShop\Module\PrestashopCheckout\Updater\CustomerUpdater;

/**
 * This controller receive ajax call when customer click on an express checkout button
 * We retrieve data from PayPal in payload and save it in PrestaShop to prefill order page
 * Then customer must be redirected to order page to choose shipping method
 */
class ps_checkoutExpressCheckoutModuleFrontController extends AbstractFrontController
{
    /**
     * @var Ps_checkout
     */
    public $module;

    /**
     * @var array
     */
    private $payload;

    /**
     * {@inheritdoc}
     */
    public function postProcess()
    {
        try {
            // We receive data in a payload not in GET/POST
            $bodyContent = file_get_contents('php://input');

            if (empty($bodyContent)) {
                throw new PsCheckoutException('Body cannot be empty', PsCheckoutException::PSCHECKOUT_VALIDATE_BODY_EMPTY);
            }

            $this->payload = json_decode($bodyContent, true);

            if (empty($this->payload)) {
                throw new PsCheckoutException('Body cannot be empty', PsCheckoutException::PSCHECKOUT_VALIDATE_BODY_EMPTY);
            }

            if (empty($this->payload['orderID']) || false === Validate::isGenericName($this->payload['orderID'])) {
                throw new PsCheckoutException('PayPal Order identifier missing or invalid', PsCheckoutException::PAYPAL_ORDER_IDENTIFIER_MISSING);
            }

            /** @var \PrestaShop\Module\PrestashopCheckout\Repository\PsCheckoutCartRepository $psCheckoutCartRepository */
            $psCheckoutCartRepository = $this->module->getService('ps_checkout.repository.pscheckoutcart');

            /** @var PsCheckoutCart|false $psCheckoutCart */
            $psCheckoutCart = $psCheckoutCartRepository->findOneByCartId((int) $this->context->cart->id);

            if (false !== $psCheckoutCart) {
                $psCheckoutCart->paypal_funding = $this->payload['fundingSource'];
                $psCheckoutCart->isExpressCheckout = true;
                $psCheckoutCart->isHostedFields = false;
                $psCheckoutCartRepository->save($psCheckoutCart);
            }

            if (false === $this->context->customer->isLogged()) {
                // @todo Extract factory in a Service.
                $this->createAndLoginCustomer(
                    $this->payload['order']['payer']['email_address'],
                    $this->payload['order']['payer']['name']['given_name'],
                    $this->payload['order']['payer']['name']['surname']
                );
            }

            $this->context->cookie->__set('paypalEmail', $this->payload['order']['payer']['email_address']);

            // Always 0 index because we are not using the paypal marketplace system
            // This index is only used in a marketplace context
            $payPalAddress = new CheckoutAddress($this->payload, new CountryAdapter($this->payload['order']['shipping']['address']['country_code']));
            $payPalAddressBuilder = new PaypalAddressBuilder($payPalAddress);

            $payPalAddressBuilder->createAddress($this->context->customer->id);
        } catch (Exception $exception) {
            $this->handleExceptionSendingToSentry($exception);

            /* @var \Psr\Log\LoggerInterface logger */
            $logger = $this->module->getService('ps_checkout.logger');
            $logger->error(
                sprintf(
                    'ExpressCheckoutController - Exception %s : %s',
                    $exception->getCode(),
                    $exception->getMessage()
                ),
                [
                    'paypal_order' => isset($this->payload['orderID']) ? $this->payload['orderID'] : null,
                ]
            );

            $this->exitWithResponse([
                'status' => false,
                'httpCode' => 500,
                'body' => $this->payload,
                'exceptionCode' => $exception->getCode(),
                'exceptionMessage' => $exception->getMessage(),
            ]);
        }

        $this->exitWithResponse([
            'status' => true,
            'httpCode' => 200,
            'body' => $this->payload,
            'exceptionCode' => null,
            'exceptionMessage' => null,
        ]);
    }

    /**
     * Handle creation and customer login
     *
     * @param string $email
     * @param string $firstName
     * @param string $lastName
     *
     * @throws PrestaShopException
     */
    private function createAndLoginCustomer(
        $email,
        $firstName,
        $lastName
    ) {
        /** @var int $idCustomerExists */
        $idCustomerExists = Customer::customerExists($email, true);

        if (0 === $idCustomerExists) {
            // @todo Extract factory in a Service.
            $customer = $this->createCustomer(
                $email,
                $firstName,
                $lastName
            );
        } else {
            $customer = new Customer($idCustomerExists);
        }

        if (method_exists($this->context, 'updateCustomer')) {
            $this->context->updateCustomer($customer);
        } else {
            CustomerUpdater::updateContextCustomer($this->context, $customer);
        }
    }

    /**
     * Create a customer
     *
     * @param string $email
     * @param string $firstName
     * @param string $lastName
     *
     * @return Customer
     *
     * @throws PsCheckoutException
     *
     * @todo Extract factory in a Service.
     */
    private function createCustomer($email, $firstName, $lastName)
    {
        $customer = new Customer();
        $customer->email = $email;
        $customer->firstname = $firstName;
        $customer->lastname = $lastName;

        if (class_exists('PrestaShop\PrestaShop\Core\Crypto\Hashing')) {
            $crypto = new PrestaShop\PrestaShop\Core\Crypto\Hashing();
            $customer->passwd = $crypto->hash(
                time() . _COOKIE_KEY_,
                _COOKIE_KEY_
            );
        } else {
            $customer->passwd = md5(time() . _COOKIE_KEY_);
        }

        try {
            $customer->save();
        } catch (Exception $exception) {
            throw new PsCheckoutException($exception->getMessage(), PsCheckoutException::PSCHECKOUT_EXPRESS_CHECKOUT_CANNOT_SAVE_CUSTOMER, $exception);
        }

        return $customer;
    }
}

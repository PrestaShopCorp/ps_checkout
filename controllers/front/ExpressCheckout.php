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
use PrestaShop\Module\PrestashopCheckout\PaypalCountryCodeMatrice;
use PrestaShop\Module\PrestashopCheckout\Repository\CountryRepository;
use PrestaShop\Module\PrestashopCheckout\Repository\PsCheckoutCartRepository;
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
                $this->exitWithResponse([
                    'httpCode' => 400,
                    'body' => 'Payload invalid',
                ]);
            }

            $this->payload = json_decode($bodyContent, true);

            if (empty($this->payload)) {
                $this->exitWithResponse([
                    'httpCode' => 400,
                    'body' => 'Payload invalid',
                ]);
            }

            if (empty($this->payload['orderID']) || false === Validate::isGenericName($this->payload['orderID'])) {
                $this->exitWithResponse([
                    'httpCode' => 400,
                    'body' => 'Payload invalid',
                ]);
            }

            /** @var PsCheckoutCartRepository $psCheckoutCartRepository */
            $psCheckoutCartRepository = $this->module->getService(PsCheckoutCartRepository::class);

            /** @var PsCheckoutCart|false $psCheckoutCart */
            $psCheckoutCart = $psCheckoutCartRepository->findOneByPayPalOrderId($this->payload['orderID']);

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
            $this->context->cookie->write();

            $this->resetContextCartAddresses();
            // Always 0 index because we are not using the paypal marketplace system
            // This index is only used in a marketplace context
            // @todo Extract factory in a Service.
            $this->createAddress(
                $this->payload['order']['payer']['name']['given_name'],
                $this->payload['order']['payer']['name']['surname'],
                $this->payload['order']['shipping']['address']['address_line_1'],
                false === empty($this->payload['order']['shipping']['address']['address_line_2']) ? $this->payload['order']['shipping']['address']['address_line_2'] : '',
                $this->payload['order']['shipping']['address']['postal_code'],
                false === empty($this->payload['order']['shipping']['address']['admin_area_1']) ? $this->payload['order']['shipping']['address']['admin_area_1'] : '',
                $this->payload['order']['shipping']['address']['admin_area_2'],
                $this->payload['order']['shipping']['address']['country_code'],
                false === empty($this->payload['order']['payer']['phone']) ? $this->payload['order']['payer']['phone']['phone_number']['national_number'] : '',
                $this->payload['orderID']
            );
        } catch (Exception $exception) {
            $this->module->getLogger()->error(
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
     * @todo Extract factory in a Service.
     *
     * @param string $email
     * @param string $firstName
     * @param string $lastName
     *
     * @return Customer
     *
     * @throws PsCheckoutException
     */
    private function createCustomer($email, $firstName, $lastName)
    {
        $customer = new Customer();
        $customer->email = $email;
        $customer->firstname = $firstName;
        $customer->lastname = $lastName;

        if (Configuration::get('PS_CHECKOUT_EXPRESS_USE_GUEST')) {
            $customer->is_guest = true;
            $customer->id_default_group = (int) Configuration::get('PS_GUEST_GROUP');
        }

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

    /**
     * Create address
     *
     * @todo Extract factory in a Service.
     *
     * @param string $firstName
     * @param string $lastName
     * @param string $address1
     * @param string $address2
     * @param string $postcode
     * @param string $state
     * @param string $city
     * @param string $countryIsoCode
     * @param string $phone
     *
     * @return bool
     *
     * @throws PsCheckoutException
     */
    private function createAddress(
        $firstName,
        $lastName,
        $address1,
        $address2,
        $postcode,
        $state,
        $city,
        $countryIsoCode,
        $phone,
        $idPaypalOrder
    ) {
        // check if country is available for delivery
        $psIsoCode = (new PaypalCountryCodeMatrice())->getPrestashopIsoCode($countryIsoCode);
        $idCountry = Country::getByIso($psIsoCode);
        $idState = 0;
        $country = new Country((int) $idCountry, null, (int) $this->context->shop->id);

        if (!Validate::isLoadedObject($country)
            || !$country->active
            || !$country->isAssociatedToShop((int) $this->context->shop->id)
            || Country::isNeedDniByCountryId($idCountry)
        ) {
            return false;
        }

        if ($country->contains_states) {
            /** @var CountryRepository $countryRepository */
            $countryRepository = $this->module->getService(CountryRepository::class);

            $idState = $countryRepository->getStateId((int) $idCountry, $state);
        }

        // check if a PayPal address already exist for the customer and not used
        $paypalAddressAlias = 'Paypal ' . $idPaypalOrder;
        $paypalAddressId = $this->addressAlreadyExist($paypalAddressAlias, $this->context->customer->id);
        $paypalAddress = new Address($paypalAddressId);
        $isPaypalValidAddressAndNotUsed = Validate::isLoadedObject($paypalAddress) && !$paypalAddress->isUsed();

        if ($isPaypalValidAddressAndNotUsed) {
            $address = $paypalAddress; // if yes, update it with the new address
        } else {
            $address = new Address(); // otherwise create a new address
        }

        $address->alias = $paypalAddressAlias;
        $address->id_customer = $this->context->customer->id;
        $address->firstname = $firstName;
        $address->lastname = $lastName;
        $address->address1 = $address1;
        $address->address2 = $address2;
        $address->postcode = $postcode;
        $address->city = $city;
        $address->id_country = $idCountry;
        $address->phone = $phone;
        $address->id_state = $idState;

        if (true !== $address->validateFields(false)) {
            return false;
        }

        try {
            $address->save();
        } catch (Exception $exception) {
            throw new PsCheckoutException($exception->getMessage(), PsCheckoutException::PSCHECKOUT_EXPRESS_CHECKOUT_CANNOT_SAVE_ADDRESS, $exception);
        }

        $this->context->cart->id_address_delivery = $address->id;
        $this->context->cart->id_address_invoice = $address->id;

        $products = $this->context->cart->getProducts();
        foreach ($products as $product) {
            $this->context->cart->setProductAddressDelivery($product['id_product'], $product['id_product_attribute'], $product['id_address_delivery'], $address->id);
        }

        return $this->context->cart->save();
    }

    /**
     * Check if address already exist, if yes return the id_address
     *
     * @param string $alias
     * @param int $id_customer
     *
     * @return int
     */
    private function addressAlreadyExist($alias, $id_customer)
    {
        $query = new DbQuery();
        $query->select('id_address');
        $query->from('address');
        $query->where('alias = \'' . pSQL($alias) . '\'');
        $query->where('id_customer = ' . (int) $id_customer);
        $query->where('deleted = 0');

        return (int) Db::getInstance()->getValue($query);
    }

    /**
     * Reset current cart address
     */
    private function resetContextCartAddresses()
    {
        $this->context->cart->id_address_delivery = 0;
        $this->context->cart->id_address_invoice = 0;
        $this->context->cart->save();
    }
}

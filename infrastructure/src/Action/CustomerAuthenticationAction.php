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

use Customer;
use PsCheckout\Core\Customer\Request\ValueObject\ExpressCheckoutRequest;
use PsCheckout\Core\Exception\PsCheckoutException;
use PsCheckout\Infrastructure\Adapter\ConfigurationInterface;
use PsCheckout\Infrastructure\Adapter\ContextInterface;
use PsCheckout\Infrastructure\Adapter\CustomerInterface;

class CustomerAuthenticationAction implements CustomerAuthenticationActionInterface
{
    /**
     * @var ConfigurationInterface
     */
    private $configuration;

    /**
     * @var CustomerInterface
     */
    private $customer;

    /**
     * @var ContextInterface
     */
    private $context;

    public function __construct(
        ConfigurationInterface $configuration,
        CustomerInterface $customer,
        ContextInterface $context
    ) {
        $this->configuration = $configuration;
        $this->customer = $customer;
        $this->context = $context;
    }

    /**
     * @param ExpressCheckoutRequest $expressCheckoutRequest
     *
     * @return void
     *
     * @throws PsCheckoutException
     */
    public function execute(ExpressCheckoutRequest $expressCheckoutRequest)
    {
        /** @var int $idCustomerExists */
        $customerId = $this->customer->customerExists($expressCheckoutRequest->getPayerEmail());

        if ($customerId === 0) {
            $customer = $this->createCustomer($expressCheckoutRequest);
        } else {
            $customer = new Customer($customerId);
        }

        $this->context->updateCustomer($customer);
    }

    /**
     * @param ExpressCheckoutRequest $expressCheckoutRequest
     *
     * @return Customer
     *
     * @throws PsCheckoutException
     */
    private function createCustomer(ExpressCheckoutRequest $expressCheckoutRequest): Customer
    {
        $customer = new Customer();
        $customer->email = $expressCheckoutRequest->getPayerEmail();
        $customer->firstname = $expressCheckoutRequest->getPayerFirstName();
        $customer->lastname = $expressCheckoutRequest->getPayerLastName();

        if ($this->configuration->get('PS_CHECKOUT_EXPRESS_USE_GUEST')) {
            $customer->is_guest = true;
            $customer->id_default_group = $this->configuration->getInteger('PS_GUEST_GROUP');
        }

        $customer->passwd = md5(time() . _COOKIE_KEY_);

        try {
            $customer->save();
        } catch (\Exception $exception) {
            throw new PsCheckoutException($exception->getMessage(), PsCheckoutException::PSCHECKOUT_EXPRESS_CHECKOUT_CANNOT_SAVE_CUSTOMER, $exception);
        }

        return $customer;
    }
}

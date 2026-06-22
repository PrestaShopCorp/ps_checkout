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

namespace PsCheckout\Core\Customer\Action;

use Exception;
use PsCheckout\Core\Customer\Request\ValueObject\ExpressCheckoutPayerData;
use PsCheckout\Core\Customer\Request\ValueObject\ExpressCheckoutShippingData;
use PsCheckout\Core\Exception\PsCheckoutException;
use PsCheckout\Infrastructure\Action\CreateOrUpdateAddressActionInterface;
use PsCheckout\Infrastructure\Action\CustomerAuthenticationActionInterface;
use PsCheckout\Infrastructure\Adapter\ContextInterface;

class ExpressCheckoutAction implements ExpressCheckoutActionInterface
{
    /**
     * @var ContextInterface
     */
    private $context;

    /**
     * @var CustomerAuthenticationActionInterface
     */
    private $customerAuthenticationAction;

    /**
     * @var CreateOrUpdateAddressActionInterface
     */
    private $createOrUpdateAddressAction;

    public function __construct(
        ContextInterface $context,
        CustomerAuthenticationActionInterface $customerAuthenticationAction,
        CreateOrUpdateAddressActionInterface $createOrUpdateAddressAction
    ) {
        $this->context = $context;
        $this->customerAuthenticationAction = $customerAuthenticationAction;
        $this->createOrUpdateAddressAction = $createOrUpdateAddressAction;
    }

    public function execute(ExpressCheckoutPayerData $payerData, ExpressCheckoutShippingData $shippingData)
    {
        $customer = $this->context->getCustomer();

        if (!$customer->isLogged()) {
            $customer->is_guest = true;
            $customer->email = $payerData->getEmail();
            $customer->firstname = $payerData->getFirstName();
            $customer->lastname = $payerData->getLastName();
            $customer->passwd = md5(time() . _COOKIE_KEY_);

            try {
                $customer->save();
            } catch (Exception $exception) {
                throw new PsCheckoutException($exception->getMessage(), PsCheckoutException::PSCHECKOUT_EXPRESS_CHECKOUT_CANNOT_SAVE_CUSTOMER, $exception);
            }

            // PS Context::updateCustomer resets cart.id_address_delivery to the customer's first
            // saved address (which does not exist yet for a brand-new guest) and clears/corrupts
            // delivery_option. Capture and restore the delivery state so the carrier selected via
            // the shipping callback survives the guest-login step.
            $cart = $this->context->getCart();
            $savedAddressId = $cart !== null ? (int) $cart->id_address_delivery : 0;
            $savedDeliveryOption = ($cart !== null && $cart->delivery_option) ? (string) $cart->delivery_option : '';

            $this->context->updateCustomer($customer);

            if ($cart !== null && $savedAddressId > 0) {
                $cart->id_address_delivery = $savedAddressId;
                $cart->delivery_option = $savedDeliveryOption;
                $cart->save();
            }
        }

        $this->context->setPayPalEmail($payerData->getEmail());

        $this->createOrUpdateAddressAction->execute($shippingData);
    }
}

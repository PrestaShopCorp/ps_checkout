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

namespace PsCheckout\Core\Order\Builder\Node\PaymentSource;

use PsCheckout\Core\Settings\Configuration\PayPalConfiguration;
use PsCheckout\Core\Util\ExperienceContextHelper;
use PsCheckout\Core\Util\PhoneParser;
use PsCheckout\Infrastructure\Adapter\ValidateInterface;

class ApplePayPaymentSourceNodeBuilder implements ApplePayPaymentSourceNodeBuilderInterface
{
    /**
     * @var PayPalConfiguration
     */
    private $payPalConfiguration;

    /**
     * @var ExperienceContextHelper
     */
    private $experienceContextHelper;

    /**
     * @var ValidateInterface
     */
    private $validate;

    /**
     * @var PhoneParser
     */
    private $phoneParser;

    /**
     * @var array<string, mixed>|null
     */
    private $cart;

    /**
     * @var string|null
     */
    private $paypalVaultId;

    /**
     * @var string|null
     */
    private $paypalCustomerId;

    /**
     * @var bool
     */
    private $savePaymentMethod = false;

    public function __construct(
        PayPalConfiguration $payPalConfiguration,
        ExperienceContextHelper $experienceContextHelper,
        ValidateInterface $validate,
        PhoneParser $phoneParser
    ) {
        $this->payPalConfiguration = $payPalConfiguration;
        $this->experienceContextHelper = $experienceContextHelper;
        $this->validate = $validate;
        $this->phoneParser = $phoneParser;
    }

    /**
     * {@inheritDoc}
     */
    public function build(): array
    {
        $data = [];

        if ($this->cart !== null && isset($this->cart['addresses']['invoice'])) {
            $invoiceAddress = $this->cart['addresses']['invoice'];

            $name = $this->experienceContextHelper->getInvoiceName($this->cart);
            if ($name !== '') {
                $data['name'] = $name;
            }

            $email = $this->experienceContextHelper->getCustomerEmail($this->cart);
            if ($email !== '' && $this->validate->isPayPalEmail($email)) {
                $data['email_address'] = $email;
            }

            $countryIso = $this->experienceContextHelper->getInvoiceCountryCode($this->cart);
            $cartId = isset($this->cart['cart']['id']) ? (int) $this->cart['cart']['id'] : null;
            $parsedPhone = $this->phoneParser->parseFromAddress($invoiceAddress, $countryIso, $cartId);
            if ($parsedPhone !== null) {
                $data['phone_number'] = [
                    'national_number' => (string) $parsedPhone->getNationalNumber(),
                    'country_code' => (string) $parsedPhone->getCountryCode(),
                ];
            }
        }

        if ($this->paypalVaultId) {
            $data['vault_id'] = $this->paypalVaultId;
        }

        $customerAttributes = $this->buildCustomerAttributes();
        if ($this->paypalCustomerId) {
            $customerAttributes['id'] = $this->paypalCustomerId;
        }
        if (!empty($customerAttributes)) {
            $data['attributes']['customer'] = $customerAttributes;
        }

        if ($this->savePaymentMethod) {
            $data['attributes']['vault']['store_in_vault'] = 'ON_SUCCESS';
        }

        if ($this->payPalConfiguration->is3dSecureEnabled()) {
            $data['attributes']['verification']['method'] = $this->payPalConfiguration->getCardFieldsContingencies();
        }

        if ($this->paypalVaultId) {
            $data['stored_credential'] = [
                'payment_initiator' => 'CUSTOMER',
                'payment_type' => 'UNSCHEDULED',
                'usage' => 'SUBSEQUENT',
            ];
        } elseif ($this->savePaymentMethod) {
            $data['stored_credential'] = [
                'payment_initiator' => 'CUSTOMER',
                'payment_type' => 'UNSCHEDULED',
                'usage' => 'FIRST',
            ];
        }

        $data['experience_context'] = $this->experienceContextHelper->buildUrlContext();

        return ['payment_source' => ['apple_pay' => $data]];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildCustomerAttributes(): array
    {
        $attributes = [];

        if ($this->cart === null || !isset($this->cart['addresses']['invoice'])) {
            return $attributes;
        }

        $address = $this->cart['addresses']['invoice'];

        if (!empty($address->firstname) || !empty($address->lastname)) {
            $attributes['name'] = [
                'given_name' => (string) $address->firstname,
                'surname' => (string) $address->lastname,
            ];
        }

        $email = $this->experienceContextHelper->getCustomerEmail($this->cart);
        if ($email !== '' && $this->validate->isPayPalEmail($email)) {
            $attributes['email_address'] = $email;
        }

        $countryIso = $this->experienceContextHelper->getInvoiceCountryCode($this->cart);
        $cartId = isset($this->cart['cart']['id']) ? (int) $this->cart['cart']['id'] : null;
        $parsedPhone = $this->phoneParser->parseFromAddress($address, $countryIso, $cartId);

        if ($parsedPhone !== null) {
            $attributes['phone'] = [
                'phone_number' => [
                    'national_number' => (string) $parsedPhone->getNationalNumber(),
                    'country_code' => (string) $parsedPhone->getCountryCode(),
                ],
                'phone_type' => $this->phoneParser->getPhoneType($parsedPhone),
            ];
        }

        return $attributes;
    }

    /**
     * {@inheritDoc}
     */
    public function setCart(array $cart)
    {
        $this->cart = $cart;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setPaypalVaultId($paypalVaultId)
    {
        $this->paypalVaultId = $paypalVaultId;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setPaypalCustomerId($paypalCustomerId)
    {
        $this->paypalCustomerId = $paypalCustomerId;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setSavePaymentMethod(bool $savePaymentMethod)
    {
        $this->savePaymentMethod = $savePaymentMethod;

        return $this;
    }
}

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

use PsCheckout\Core\Util\ExperienceContextHelper;
use PsCheckout\Core\Util\PhoneParser;
use PsCheckout\Infrastructure\Adapter\ValidateInterface;

class VenmoPaymentSourceNodeBuilder implements VenmoPaymentSourceNodeBuilderInterface
{
    /**
     * @var string
     */
    private $paypalVaultId;

    /**
     * @var string
     */
    private $paypalCustomerId;

    /**
     * @var bool
     */
    private $savePaymentMethod;

    /**
     * @var bool
     */
    private $isExpressCheckout = false;

    /**
     * @var array|null
     */
    private $cart;

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

    public function __construct(
        ExperienceContextHelper $experienceContextHelper,
        ValidateInterface $validate,
        PhoneParser $phoneParser
    ) {
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

        if ($this->cart !== null) {
            $email = $this->experienceContextHelper->getCustomerEmail($this->cart);
            if ($email !== '' && $this->validate->isPayPalEmail($email)) {
                $data['email_address'] = $email;
            }
        }

        $customerAttributes = $this->buildCustomerAttributes();
        if ($this->savePaymentMethod && $this->paypalCustomerId) {
            $customerAttributes['id'] = $this->paypalCustomerId;
        }
        if (!empty($customerAttributes)) {
            $data['attributes']['customer'] = $customerAttributes;
        }

        if ($this->savePaymentMethod) {
            $data['attributes']['vault'] = [
                'store_in_vault' => 'ON_SUCCESS',
                'usage_pattern' => 'IMMEDIATE',
                'usage_type' => 'MERCHANT',
                'customer_type' => 'CONSUMER',
                'permit_multiple_payment_tokens' => false,
            ];
        }

        if ($this->paypalVaultId) {
            $data['vault_id'] = $this->paypalVaultId;
        }

        $data['experience_context'] = [
            'brand_name' => $this->experienceContextHelper->getBrandName(),
            'shipping_preference' => ExperienceContextHelper::getShippingPreference($this->cart ?? []),
            'user_action' => (!$this->isExpressCheckout && $this->cart !== null) ? 'PAY_NOW' : 'CONTINUE',
        ];

        return [
            'payment_source' => [
                'venmo' => $data,
            ],
        ];
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

    /** {@inheritDoc} */
    public function setPaypalVaultId($paypalVaultId): self
    {
        $this->paypalVaultId = $paypalVaultId;

        return $this;
    }

    /** {@inheritDoc} */
    public function setPaypalCustomerId($paypalCustomerId): self
    {
        $this->paypalCustomerId = $paypalCustomerId;

        return $this;
    }

    /** {@inheritDoc} */
    public function setSavePaymentMethod(bool $savePaymentMethod): self
    {
        $this->savePaymentMethod = $savePaymentMethod;

        return $this;
    }

    /** {@inheritDoc} */
    public function setCart(array $cart)
    {
        $this->cart = $cart;

        return $this;
    }

    /** {@inheritDoc} */
    public function setIsExpressCheckout(bool $isExpressCheckout): self
    {
        $this->isExpressCheckout = $isExpressCheckout;

        return $this;
    }
}

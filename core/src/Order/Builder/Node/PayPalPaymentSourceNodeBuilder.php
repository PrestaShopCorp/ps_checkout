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

namespace PsCheckout\Core\Order\Builder\Node;

use PsCheckout\Core\Util\ExperienceContextHelper;
use PsCheckout\Core\Util\PhoneParser;
use PsCheckout\Infrastructure\Adapter\ValidateInterface;
use Psr\Log\LoggerInterface;

class PayPalPaymentSourceNodeBuilder implements PayPalPaymentSourceNodeBuilderInterface
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
    private $shippingAddressExists;

    /**
     * @var bool
     */
    private $isVirtualCart;

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
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ValidateInterface
     */
    private $validate;

    /**
     * @var PhoneParser
     */
    private $phoneParser;

    /**
     * @var string
     */
    private $fundingSource;

    public function __construct(
        ExperienceContextHelper $experienceContextHelper,
        LoggerInterface $logger,
        ValidateInterface $validate,
        PhoneParser $phoneParser
    ) {
        $this->experienceContextHelper = $experienceContextHelper;
        $this->logger = $logger;
        $this->validate = $validate;
        $this->phoneParser = $phoneParser;
    }

    /**
     * {@inheritDoc}
     */
    public function build(): array
    {
        $data = [];

        if ($this->savePaymentMethod) {
            $data['attributes']['vault'] = [
                'store_in_vault' => 'ON_SUCCESS',
                'usage_pattern' => 'IMMEDIATE',
                'usage_type' => 'MERCHANT',
                'customer_type' => 'CONSUMER',
                'permit_multiple_payment_tokens' => false,
            ];
            if ($this->paypalCustomerId) {
                $data['attributes']['customer'] = [
                    'id' => $this->paypalCustomerId,
                ];
            }
        }

        if ($this->cart !== null) {
            $data = array_merge($data, $this->buildPayerData());
        }

        switch ($this->fundingSource) {
            case 'paylater':
                $paymentMethodSelected = 'PAYPAL_PAY_LATER';

                break;
            case 'credit':
                $paymentMethodSelected = 'PAYPAL_CREDIT';

                break;
            default:
                $paymentMethodSelected = 'PAYPAL';
        }

        $data['experience_context'] = array_merge(
            [
                'brand_name' => $this->experienceContextHelper->getBrandName(),
                'shipping_preference' => $this->isVirtualCart ? 'NO_SHIPPING' : ($this->shippingAddressExists ? 'SET_PROVIDED_ADDRESS' : 'GET_FROM_FILE'),
                'contact_preference' => $this->isExpressCheckout ? 'UPDATE_CONTACT_INFO' : 'NO_CONTACT_INFO',
                'landing_page' => 'LOGIN',
                'payment_method_selected' => $paymentMethodSelected,
                'user_action' => $this->isExpressCheckout ? 'CONTINUE' : 'PAY_NOW',
            ],
            $this->experienceContextHelper->buildUrlContext()
        );

        if (empty($data)) {
            return [];
        }

        return [
            'payment_source' => [
                'paypal' => $data,
            ],
        ];
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
    public function setShippingAddressExists(bool $shippingAddressExists): self
    {
        $this->shippingAddressExists = $shippingAddressExists;

        return $this;
    }

    /** {@inheritDoc} */
    public function setVirtualCart(bool $isVirtualCart): self
    {
        $this->isVirtualCart = $isVirtualCart;

        return $this;
    }

    /** {@inheritDoc} */
    public function setCart(array $cart): self
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

    /** {@inheritDoc} */
    public function setFundingSource(string $fundingSource): self
    {
        $this->fundingSource = $fundingSource;

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    private function buildPayerData(): array
    {
        $data = [];

        if (!isset($this->cart['addresses']['invoice'])) {
            $this->logger->warning('Invoice address is missing in the cart.');

            return $data;
        }

        $invoiceAddress = $this->cart['addresses']['invoice'];
        $countryIsoCode = $this->experienceContextHelper->getInvoiceCountryCode($this->cart);

        $data['name'] = [
            'given_name' => isset($invoiceAddress->firstname) ? (string) $invoiceAddress->firstname : '',
            'surname' => isset($invoiceAddress->lastname) ? (string) $invoiceAddress->lastname : '',
        ];

        $data['address'] = $this->experienceContextHelper->buildInvoicePortableAddress($this->cart);

        $email = $this->experienceContextHelper->getCustomerEmail($this->cart);
        if ($email !== '' && $this->validate->isPayPalEmail($email)) {
            $data['email_address'] = $email;
        }

        if (!empty($this->cart['customer']->birthday) && $this->cart['customer']->birthday !== '0000-00-00') {
            $data['birth_date'] = (string) $this->cart['customer']->birthday;
        }

        $cartId = isset($this->cart['cart']['id']) ? (int) $this->cart['cart']['id'] : null;
        $parsedPhone = $this->phoneParser->parseFromAddress($invoiceAddress, $countryIsoCode, $cartId);
        if ($parsedPhone !== null) {
            $data['phone'] = [
                'phone_number' => [
                    'national_number' => (string) $parsedPhone->getNationalNumber(),
                    'country_code' => (string) $parsedPhone->getCountryCode(),
                ],
                'phone_type' => $this->phoneParser->getPhoneType($parsedPhone),
            ];
        }

        return array_filter($data);
    }
}

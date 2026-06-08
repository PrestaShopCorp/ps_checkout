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

use PsCheckout\Core\Exception\PsCheckoutException;
use PsCheckout\Core\Order\Builder\CheckoutContextInterface;
use PsCheckout\Core\Order\Builder\PaymentSourceNodeBuilderInterface;
use PsCheckout\Core\Settings\Configuration\PayPalConfiguration;
use PsCheckout\Core\Util\ExperienceContextHelper;
use PsCheckout\Core\Util\PhoneParser;
use PsCheckout\Infrastructure\Adapter\ValidateInterface;
use Psr\Log\LoggerInterface;

class CardPaymentSourceNodeBuilder implements PaymentSourceNodeBuilderInterface
{
    /**
     * @var PayPalConfiguration
     */
    private $paypalConfiguration;

    /**
     * @var ExperienceContextHelper
     */
    private $experienceContextHelper;

    /**
     * @var PhoneParser
     */
    private $phoneParser;

    /**
     * @var ValidateInterface
     */
    private $validate;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        PayPalConfiguration $paypalConfiguration,
        ExperienceContextHelper $experienceContextHelper,
        PhoneParser $phoneParser,
        ValidateInterface $validate,
        LoggerInterface $logger
    ) {
        $this->paypalConfiguration = $paypalConfiguration;
        $this->experienceContextHelper = $experienceContextHelper;
        $this->phoneParser = $phoneParser;
        $this->validate = $validate;
        $this->logger = $logger;
    }

    public function supports(string $fundingSource): bool
    {
        return $fundingSource === 'card';
    }

    /**
     * {@inheritDoc}
     */
    public function build(CheckoutContextInterface $context): array
    {
        $cart = $context->getCart();
        $address = $cart['addresses']['invoice'];

        $node = [
            'payment_source' => [
                'card' => [
                    'name' => $address->firstname . ' ' . $address->lastname,
                    'billing_address' => $this->experienceContextHelper->buildInvoicePortableAddress($cart),
                ],
            ],
        ];

        if ($this->paypalConfiguration->is3dSecureEnabled()) {
            $node['payment_source']['card']['attributes']['verification']['method'] = $this->paypalConfiguration->getCardFieldsContingencies();
        }

        if ($context->getPaypalVaultId()) {
            unset($node['payment_source']['card']['billing_address']);
            $node['payment_source']['card']['vault_id'] = $context->getPaypalVaultId();
        }

        $countryIso = $this->experienceContextHelper->getInvoiceCountryCode($cart);
        $customerAttributes = $this->buildCustomerAttributes($address, $countryIso, $cart);
        if ($context->getPaypalCustomerId()) {
            $customerAttributes['id'] = $context->getPaypalCustomerId();
        }
        if (!empty($customerAttributes)) {
            $node['payment_source']['card']['attributes']['customer'] = $customerAttributes;
        }

        if ($context->isSavePaymentMethod()) {
            $node['payment_source']['card']['attributes']['vault'] = [
                'store_in_vault' => 'ON_SUCCESS',
            ];
        }

        if ($context->getPaypalVaultId()) {
            $node['payment_source']['card']['stored_credential'] = [
                'payment_initiator' => 'CUSTOMER',
                'payment_type' => 'UNSCHEDULED',
                'usage' => 'SUBSEQUENT',
            ];
        } elseif ($context->isSavePaymentMethod()) {
            $node['payment_source']['card']['stored_credential'] = [
                'payment_initiator' => 'CUSTOMER',
                'payment_type' => 'UNSCHEDULED',
                'usage' => 'FIRST',
            ];
        }

        $node['payment_source']['card']['experience_context'] = $this->experienceContextHelper->buildUrlContext();

        return $node;
    }

    /**
     * @param mixed $address
     * @param array<string, mixed> $cart
     *
     * @return array<string, mixed>
     *
     * @throws PsCheckoutException
     */
    private function buildCustomerAttributes($address, string $countryIso, array $cart): array
    {
        $attributes = [];

        if (!empty($address->firstname) || !empty($address->lastname)) {
            $attributes['name'] = [
                'given_name' => (string) $address->firstname,
                'surname' => (string) $address->lastname,
            ];
        }

        $email = $this->experienceContextHelper->getCustomerEmail($cart);
        if ($email === '' || !$this->validate->isPayPalEmail($email)) {
            $this->logger->warning('Valid email is required for card payment.');

            throw new PsCheckoutException('Valid email is required for card payment.', PsCheckoutException::CART_CUSTOMER_EMAIL_INVALID);
        }
        $attributes['email_address'] = $email;

        $rawPhone = !empty($address->phone)
            ? $address->phone
            : (!empty($address->phone_mobile) ? $address->phone_mobile : '');

        if (empty($rawPhone)) {
            $this->logger->warning('Phone number is required for card payment.');

            throw new PsCheckoutException('Phone number is required for card payment.', PsCheckoutException::CART_CUSTOMER_PHONE_INVALID);
        }

        $cartId = isset($cart['cart']['id']) ? (int) $cart['cart']['id'] : null;
        $parsedPhone = $this->phoneParser->parsePhone($rawPhone, $countryIso, ['id_cart' => $cartId]);

        if ($parsedPhone === null) {
            $this->logger->warning('Phone number is not valid for card payment.', [
                'id_cart' => $cartId,
                'phone' => $rawPhone,
                'country' => $countryIso,
            ]);

            throw new PsCheckoutException('Phone number is not valid for card payment.', PsCheckoutException::CART_CUSTOMER_PHONE_INVALID);
        }

        $attributes['phone'] = [
            'phone_number' => [
                'national_number' => (string) $parsedPhone->getNationalNumber(),
                'country_code' => (string) $parsedPhone->getCountryCode(),
            ],
            'phone_type' => $this->phoneParser->getPhoneType($parsedPhone),
        ];

        return $attributes;
    }
}

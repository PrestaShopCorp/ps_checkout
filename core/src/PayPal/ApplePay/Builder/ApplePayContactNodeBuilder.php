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

namespace PsCheckout\Core\PayPal\ApplePay\Builder;

use Psr\Log\LoggerInterface;
use PsCheckout\Core\Order\Builder\CheckoutContextInterface;
use PsCheckout\Core\Util\ExperienceContextHelper;
use PsCheckout\Core\Util\PhoneParser;
use PsCheckout\Infrastructure\Adapter\ValidateInterface;
use PsCheckout\Infrastructure\Repository\CountryRepositoryInterface;
use PsCheckout\Infrastructure\Service\PaypalStateNameResolver;

class ApplePayContactNodeBuilder implements ApplePayNodeBuilderInterface
{
    /**
     * @var ExperienceContextHelper
     */
    private $experienceContextHelper;

    /**
     * @var CountryRepositoryInterface
     */
    private $countryRepository;

    /**
     * @var PaypalStateNameResolver
     */
    private $stateNameResolver;

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
        ExperienceContextHelper $experienceContextHelper,
        CountryRepositoryInterface $countryRepository,
        PaypalStateNameResolver $stateNameResolver,
        PhoneParser $phoneParser,
        ValidateInterface $validate,
        LoggerInterface $logger
    ) {
        $this->experienceContextHelper = $experienceContextHelper;
        $this->countryRepository = $countryRepository;
        $this->stateNameResolver = $stateNameResolver;
        $this->phoneParser = $phoneParser;
        $this->validate = $validate;
        $this->logger = $logger;
    }

    /**
     * {@inheritDoc}
     */
    public function build(CheckoutContextInterface $context): array
    {
        /** @var array<string, mixed> $cart */
        $cart = $context->getCart();
        $isVirtual = $context->isVirtualCart();

        $data = [
            'required_billing_contact_fields' => ['name', 'email'],
            'required_shipping_contact_fields' => $isVirtual ? [] : ['name', 'postalAddress'],
            'shipping_contact_editing_mode' => 'enabled',
        ];

        $billingContact = $this->buildBillingContact($cart);
        if (!empty($billingContact)) {
            $data['billing_contact'] = $billingContact;
        }

        if (!$isVirtual && $context->hasShippingAddress()) {
            $shippingContact = $this->buildShippingContact($cart);
            if (!empty($shippingContact)) {
                $data['shipping_contact'] = $shippingContact;
            }
        }

        return $data;
    }

    /**
     * @param array<string, mixed> $cart
     *
     * @return array<string, mixed>
     */
    private function buildBillingContact(array $cart): array
    {
        /** @var array<string, mixed> $addresses */
        $addresses = $cart['addresses'] ?? [];
        if (!isset($addresses['invoice'])) {
            $this->logger->warning('ApplePayContactNodeBuilder: invoice address is missing, billing_contact omitted');

            return [];
        }

        /** @var \stdClass $address */
        $address = $addresses['invoice'];

        /** @var string $firstName */
        $firstName = $address->firstname ?? '';
        /** @var string $lastName */
        $lastName = $address->lastname ?? '';

        if (empty($firstName) && empty($lastName)) {
            $this->logger->warning('ApplePayContactNodeBuilder: invoice address has no name, billing_contact omitted');

            return [];
        }

        /** @var array<string, mixed> $cartData */
        $cartData = $cart['cart'] ?? [];
        /** @var int $rawCartId */
        $rawCartId = $cartData['id'] ?? 0;
        $cartId = $rawCartId > 0 ? $rawCartId : null;

        $countryIso = $this->experienceContextHelper->getInvoiceCountryCode($cart);

        $portableAddress = $this->experienceContextHelper->buildInvoicePortableAddress($cart);
        if (empty($portableAddress)) {
            return [];
        }

        $contact = [
            'given_name' => $firstName,
            'family_name' => $lastName,
        ];

        $email = $this->experienceContextHelper->getCustomerEmail($cart);
        if ($email !== '' && $this->validate->isPayPalEmail($email)) {
            $contact['email_address'] = $email;
        }

        $parsedPhone = $this->phoneParser->parseFromAddress($address, $countryIso, $cartId);
        if ($parsedPhone !== null) {
            $contact['phone_number'] = '+' . $parsedPhone->getCountryCode() . $parsedPhone->getNationalNumber();
        }

        $addressLines = array_values(array_filter([
            trim((string) ($portableAddress['address_line_1'] ?? '')),
            trim((string) ($portableAddress['address_line_2'] ?? '')),
        ]));
        if (!empty($addressLines)) {
            $contact['address_lines'] = $addressLines;
        }

        if (!empty($portableAddress['admin_area_2'])) {
            $contact['locality'] = $portableAddress['admin_area_2'];
        }
        if (!empty($portableAddress['postal_code'])) {
            $contact['postal_code'] = $portableAddress['postal_code'];
        }
        if (!empty($portableAddress['admin_area_1'])) {
            $contact['administrative_area'] = $portableAddress['admin_area_1'];
        }
        if (!empty($portableAddress['country_code'])) {
            $contact['country_code'] = $portableAddress['country_code'];
        }

        return $contact;
    }

    /**
     * @param array<string, mixed> $cart
     *
     * @return array<string, mixed>
     */
    private function buildShippingContact(array $cart): array
    {
        /** @var array<string, mixed> $addresses */
        $addresses = $cart['addresses'] ?? [];
        if (!isset($addresses['shipping'])) {
            return [];
        }

        /** @var \stdClass $address */
        $address = $addresses['shipping'];

        /** @var string $firstName */
        $firstName = $address->firstname ?? '';
        /** @var string $lastName */
        $lastName = $address->lastname ?? '';

        if (empty($firstName) && empty($lastName)) {
            $this->logger->warning('ApplePayContactNodeBuilder: shipping address has no name, shipping_contact omitted');

            return [];
        }

        if (!isset($address->id_country)) {
            return [];
        }

        /** @var array<string, mixed> $cartData */
        $cartData = $cart['cart'] ?? [];
        /** @var int $rawCartId */
        $rawCartId = $cartData['id'] ?? 0;
        $cartId = $rawCartId > 0 ? $rawCartId : null;

        /** @var int $idCountry */
        $idCountry = $address->id_country ?? 0;
        $countryIso = $this->countryRepository->getCountryIsoCodeById($idCountry);

        if (!preg_match('/^[A-Za-z]{2}$/', $countryIso)) {
            $this->logger->warning('ApplePayContactNodeBuilder: invalid country code for shipping address, shipping_contact omitted', [
                'id_country' => $address->id_country,
            ]);

            return [];
        }

        /** @var int $idState */
        $idState = $address->id_state ?? 0;
        $stateName = $this->stateNameResolver->resolve($countryIso, $idState);

        $contact = [
            'given_name' => $firstName,
            'family_name' => $lastName,
        ];

        $parsedPhone = $this->phoneParser->parseFromAddress($address, $countryIso, $cartId);
        if ($parsedPhone !== null) {
            $contact['phone_number'] = '+' . $parsedPhone->getCountryCode() . $parsedPhone->getNationalNumber();
        }

        /** @var string $addr1 */
        $addr1 = $address->address1 ?? '';
        /** @var string $addr2 */
        $addr2 = $address->address2 ?? '';
        $addressLines = array_values(array_filter([trim($addr1), trim($addr2)]));
        if (!empty($addressLines)) {
            $contact['address_lines'] = $addressLines;
        }

        /** @var string $city */
        $city = $address->city ?? '';
        if (!empty($city)) {
            $contact['locality'] = $city;
        }
        /** @var string $postcode */
        $postcode = $address->postcode ?? '';
        if (!empty($postcode)) {
            $contact['postal_code'] = $postcode;
        }
        if (!empty($stateName)) {
            $contact['administrative_area'] = $stateName;
        }
        $contact['country_code'] = $countryIso;

        return $contact;
    }
}

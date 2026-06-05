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
use PsCheckout\Core\Settings\Configuration\PayPalConfiguration;
use PsCheckout\Core\Util\PhoneParser;
use PsCheckout\Infrastructure\Adapter\LinkInterface;
use PsCheckout\Infrastructure\Adapter\ValidateInterface;
use PsCheckout\Infrastructure\Repository\CountryRepositoryInterface;
use PsCheckout\Infrastructure\Repository\StateRepositoryInterface;
use PsCheckout\Utility\Payload\OrderPayloadUtility;
use Psr\Log\LoggerInterface;

class CardPaymentSourceNodeBuilder implements CardPaymentSourceNodeBuilderInterface
{
    /**
     * @var array
     */
    private $cart;

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
     * @var PayPalConfiguration
     */
    private $paypalConfiguration;

    /**
     * @var CountryRepositoryInterface
     */
    private $countryRepository;

    /**
     * @var StateRepositoryInterface
     */
    private $stateRepository;

    /**
     * @var LinkInterface
     */
    private $link;

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
        CountryRepositoryInterface $countryRepository,
        StateRepositoryInterface $stateRepository,
        LinkInterface $link,
        PhoneParser $phoneParser,
        ValidateInterface $validate,
        LoggerInterface $logger
    ) {
        $this->paypalConfiguration = $paypalConfiguration;
        $this->countryRepository = $countryRepository;
        $this->stateRepository = $stateRepository;
        $this->link = $link;
        $this->phoneParser = $phoneParser;
        $this->validate = $validate;
        $this->logger = $logger;
    }

    /**
     * {@inheritDoc}
     */
    public function build(): array
    {
        $address = $this->cart['addresses']['invoice'];

        $countryIso = $this->countryRepository->getCountryIsoCodeById($address->id_country);
        $stateName = $countryIso === 'US' ?
            $this->stateRepository->getIsoById($address->id_state)
            : $this->stateRepository->getNameById($address->id_state);

        $node = [
            'payment_source' => [
                'card' => [
                    'name' => $this->cart['addresses']['invoice']->firstname . ' ' . $this->cart['addresses']['invoice']->lastname,
                    'billing_address' => OrderPayloadUtility::getAddressPortable($address, $countryIso, $stateName),
                ],
            ],
        ];

        if ($this->paypalConfiguration->is3dSecureEnabled()) {
            $node['payment_source']['card']['attributes']['verification']['method'] = $this->paypalConfiguration->getCardFieldsContingencies();
        }

        if ($this->paypalVaultId) {
            unset($node['payment_source']['card']['billing_address']);
            $node['payment_source']['card']['vault_id'] = $this->paypalVaultId;
        }

        $customerAttributes = $this->buildCustomerAttributes($address, $countryIso);
        if ($this->paypalCustomerId) {
            $customerAttributes['id'] = $this->paypalCustomerId;
        }
        if (!empty($customerAttributes)) {
            $node['payment_source']['card']['attributes']['customer'] = $customerAttributes;
        }

        if ($this->savePaymentMethod) {
            $node['payment_source']['card']['attributes']['vault'] = [
                'store_in_vault' => 'ON_SUCCESS',
            ];
        }

        if ($this->paypalVaultId) {
            $node['payment_source']['card']['stored_credential'] = [
                'payment_initiator' => 'CUSTOMER',
                'payment_type' => 'UNSCHEDULED',
                'usage' => 'SUBSEQUENT',
            ];
        } elseif ($this->savePaymentMethod) {
            $node['payment_source']['card']['stored_credential'] = [
                'payment_initiator' => 'CUSTOMER',
                'payment_type' => 'UNSCHEDULED',
                'usage' => 'FIRST',
            ];
        }

        $node['payment_source']['card']['experience_context'] = [
            'return_url' => $this->link->getModuleLink('validate'),
            'cancel_url' => $this->link->getModuleLink('cancel'),
        ];

        return $node;
    }

    /**
     * @param mixed $address
     *
     * @return array<string, mixed>
     *
     * @throws PsCheckoutException
     */
    private function buildCustomerAttributes($address, string $countryIso): array
    {
        $attributes = [];

        if (!empty($address->firstname) || !empty($address->lastname)) {
            $attributes['name'] = [
                'given_name' => (string) $address->firstname,
                'surname' => (string) $address->lastname,
            ];
        }

        if (!isset($this->cart['customer']->email)
            || !$this->validate->isPayPalEmail($this->cart['customer']->email)
        ) {
            $this->logger->warning('Valid email is required for card payment.');

            throw new PsCheckoutException('Valid email is required for card payment.', PsCheckoutException::CART_CUSTOMER_EMAIL_INVALID);
        }
        $attributes['email_address'] = (string) $this->cart['customer']->email;

        $rawPhone = !empty($address->phone)
            ? $address->phone
            : (!empty($address->phone_mobile) ? $address->phone_mobile : '');

        if (empty($rawPhone)) {
            $this->logger->warning('Phone number is required for card payment.');

            throw new PsCheckoutException('Phone number is required for card payment.', PsCheckoutException::CART_CUSTOMER_PHONE_INVALID);
        }

        $cartId = isset($this->cart['cart']['id']) ? (int) $this->cart['cart']['id'] : null;
        $parsedPhone = $this->phoneParser->parsePhone($rawPhone, $countryIso, ['id_cart' => $cartId]);

        if ($parsedPhone === null) {
            $this->logger->warning('Phone number is not valid for card payment.', [
                'id_cart' => $cartId,
                'phone' => $rawPhone,
            ]);

            throw new PsCheckoutException('Phone number is not valid for card payment.', PsCheckoutException::CART_CUSTOMER_PHONE_INVALID);
        }

        $attributes['phone'] = [
            'phone_number' => [
                'national_number' => (string) $parsedPhone->getNationalNumber(),
            ],
            'phone_type' => $this->phoneParser->getPhoneType($parsedPhone),
        ];

        return $attributes;
    }

    /**
     * {@inheritDoc}
     */
    public function setCart(array $cart): self
    {
        $this->cart = $cart;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setPaypalVaultId($paypalVaultId): self
    {
        $this->paypalVaultId = $paypalVaultId;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setPaypalCustomerId($paypalCustomerId): self
    {
        $this->paypalCustomerId = $paypalCustomerId;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setSavePaymentMethod(bool $savePaymentMethod): self
    {
        $this->savePaymentMethod = $savePaymentMethod;

        return $this;
    }
}

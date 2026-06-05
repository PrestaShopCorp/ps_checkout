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

use PsCheckout\Core\Order\Builder\CheckoutContextInterface;
use PsCheckout\Core\Order\Builder\PaymentSourceNodeBuilderInterface;
use PsCheckout\Core\Util\PhoneParser;
use PsCheckout\Infrastructure\Adapter\ConfigurationInterface;
use PsCheckout\Infrastructure\Adapter\LinkInterface;
use PsCheckout\Infrastructure\Adapter\ValidateInterface;
use PsCheckout\Infrastructure\Repository\CountryRepositoryInterface;
use PsCheckout\Utility\Common\StringUtility;

class VenmoPaymentSourceNodeBuilder implements PaymentSourceNodeBuilderInterface
{
    /**
     * @var ConfigurationInterface
     */
    private $configuration;

    /**
     * @var LinkInterface
     */
    private $link;

    /**
     * @var ValidateInterface
     */
    private $validate;

    /**
     * @var PhoneParser
     */
    private $phoneParser;

    /**
     * @var CountryRepositoryInterface
     */
    private $countryRepository;

    public function __construct(
        ConfigurationInterface $configuration,
        LinkInterface $link,
        ValidateInterface $validate,
        PhoneParser $phoneParser,
        CountryRepositoryInterface $countryRepository
    ) {
        $this->configuration = $configuration;
        $this->link = $link;
        $this->validate = $validate;
        $this->phoneParser = $phoneParser;
        $this->countryRepository = $countryRepository;
    }

    public function supports(string $fundingSource): bool
    {
        return $fundingSource === 'venmo';
    }

    /**
     * {@inheritDoc}
     */
    public function build(CheckoutContextInterface $context): array
    {
        $data = [];

        $cart = $context->getCart();
        if (!$context->isExpressCheckout() && !$context->isUpdate()
            && isset($cart['customer']->email)
            && $this->validate->isPayPalEmail($cart['customer']->email)
        ) {
            $data['email_address'] = (string) $cart['customer']->email;
        }

        $customerAttributes = $this->buildCustomerAttributes($cart);
        if ($context->isSavePaymentMethod() && $context->getPaypalCustomerId()) {
            $customerAttributes['id'] = $context->getPaypalCustomerId();
        }
        if (!empty($customerAttributes)) {
            $data['attributes']['customer'] = $customerAttributes;
        }

        if ($context->isSavePaymentMethod()) {
            $data['attributes']['vault'] = [
                'store_in_vault' => 'ON_SUCCESS',
                'usage_pattern' => 'IMMEDIATE',
                'usage_type' => 'MERCHANT',
                'customer_type' => 'CONSUMER',
                'permit_multiple_payment_tokens' => false,
            ];
        }

        if ($context->getPaypalVaultId()) {
            $data['vault_id'] = $context->getPaypalVaultId();
        }

        $shippingPreference = $context->isVirtualCart() ? 'NO_SHIPPING' : ($context->hasShippingAddress() ? 'SET_PROVIDED_ADDRESS' : 'GET_FROM_FILE');

        $data['experience_context'] = [
            'brand_name' => StringUtility::normalizeBrandName((string) $this->configuration->get('PS_SHOP_NAME')),
            'return_url' => $this->link->getModuleLink('validate'),
            'cancel_url' => $this->link->getModuleLink('cancel'),
            'shipping_preference' => $shippingPreference,
            'user_action' => (!$context->isExpressCheckout() && !$context->isUpdate()) ? 'PAY_NOW' : 'CONTINUE',
        ];

        if ($shippingPreference === 'GET_FROM_FILE' && $context->getCartId()) {
            $data['experience_context']['order_update_callback_config'] = [
                'callback_events' => ['SHIPPING_ADDRESS', 'SHIPPING_OPTIONS'],
                'callback_url' => $this->link->getModuleLink('shipping', ['id_cart' => $context->getCartId()]),
            ];
        }

        return [
            'payment_source' => [
                'venmo' => $data,
            ],
        ];
    }

    /**
     * @param array<string, mixed> $cart
     *
     * @return array<string, mixed>
     */
    private function buildCustomerAttributes(array $cart): array
    {
        $attributes = [];

        if (!isset($cart['addresses']['invoice'])) {
            return $attributes;
        }

        $address = $cart['addresses']['invoice'];

        if (!empty($address->firstname) || !empty($address->lastname)) {
            $attributes['name'] = [
                'given_name' => (string) $address->firstname,
                'surname' => (string) $address->lastname,
            ];
        }

        if (isset($cart['customer']->email)
            && $this->validate->isPayPalEmail($cart['customer']->email)
        ) {
            $attributes['email_address'] = (string) $cart['customer']->email;
        }

        $countryIso = isset($address->id_country)
            ? $this->countryRepository->getCountryIsoCodeById($address->id_country)
            : '';

        $cartId = isset($cart['cart']['id']) ? (int) $cart['cart']['id'] : null;
        $parsedPhone = $this->phoneParser->parseFromAddress($address, $countryIso, $cartId);

        if ($parsedPhone !== null) {
            $attributes['phone'] = [
                'phone_number' => [
                    'national_number' => (string) $parsedPhone->getNationalNumber(),
                ],
                'phone_type' => $this->phoneParser->getPhoneType($parsedPhone),
            ];
        }

        return $attributes;
    }
}

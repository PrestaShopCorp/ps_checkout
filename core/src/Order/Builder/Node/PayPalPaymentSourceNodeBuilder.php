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

use PsCheckout\Core\Order\Builder\CheckoutContextInterface;
use PsCheckout\Core\Order\Builder\PaymentSourceNodeBuilderInterface;
use PsCheckout\Core\Util\PhoneParser;
use PsCheckout\Infrastructure\Adapter\ConfigurationInterface;
use PsCheckout\Infrastructure\Adapter\LinkInterface;
use PsCheckout\Infrastructure\Adapter\ValidateInterface;
use PsCheckout\Infrastructure\Repository\CountryRepositoryInterface;
use PsCheckout\Infrastructure\Repository\StateRepositoryInterface;
use PsCheckout\Utility\Common\StringUtility;
use PsCheckout\Utility\Payload\OrderPayloadUtility;
use Psr\Log\LoggerInterface;

class PayPalPaymentSourceNodeBuilder implements PaymentSourceNodeBuilderInterface
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
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ValidateInterface
     */
    private $validate;

    /**
     * @var CountryRepositoryInterface
     */
    private $countryRepository;

    /**
     * @var StateRepositoryInterface
     */
    private $stateRepository;

    /**
     * @var PhoneParser
     */
    private $phoneParser;

    /**
     * @var string
     */
    private $fundingSource;

    public function __construct(
        ConfigurationInterface $configuration,
        LinkInterface $link,
        LoggerInterface $logger,
        ValidateInterface $validate,
        CountryRepositoryInterface $countryRepository,
        StateRepositoryInterface $stateRepository,
        PhoneParser $phoneParser
    ) {
        $this->configuration = $configuration;
        $this->link = $link;
        $this->logger = $logger;
        $this->validate = $validate;
        $this->countryRepository = $countryRepository;
        $this->stateRepository = $stateRepository;
        $this->phoneParser = $phoneParser;
    }

    public function supports(string $fundingSource): bool
    {
        return in_array($fundingSource, ['paypal', 'paylater', 'credit'], true);
    }

    /**
     * {@inheritDoc}
     */
    public function build(CheckoutContextInterface $context): array
    {
        $data = [];

        if ($context->getPaypalVaultId()) {
            $data['vault_id'] = $context->getPaypalVaultId();
        }

        if ($context->isSavePaymentMethod()) {
            $data['attributes']['vault'] = [
                'store_in_vault' => 'ON_SUCCESS',
                'usage_pattern' => 'IMMEDIATE',
                'usage_type' => 'MERCHANT',
                'customer_type' => 'CONSUMER',
                'permit_multiple_payment_tokens' => false,
            ];
            if ($context->getPaypalCustomerId()) {
                $data['attributes']['customer'] = [
                    'id' => $context->getPaypalCustomerId(),
                ];
            }
        }

        if (!$context->isExpressCheckout() && !$context->isUpdate()) {
            $data = array_merge($data, $this->buildPayerData($context->getCart()));
        }

        switch ($context->getFundingSource()) {
            case 'paylater':
                $paymentMethodSelected = 'PAYPAL_PAY_LATER';

                break;
            case 'credit':
                $paymentMethodSelected = 'PAYPAL_CREDIT';

                break;
            default:
                $paymentMethodSelected = 'PAYPAL';
        }

        $shippingPreference = $context->isVirtualCart() ? 'NO_SHIPPING' : ($context->hasShippingAddress() ? 'SET_PROVIDED_ADDRESS' : 'GET_FROM_FILE');

        $data['experience_context'] = [
            'brand_name' => StringUtility::normalizeBrandName((string) $this->configuration->get('PS_SHOP_NAME')),
            'shipping_preference' => $shippingPreference,
            'contact_preference' => $context->isExpressCheckout() ? 'UPDATE_CONTACT_INFO' : 'NO_CONTACT_INFO',
            'landing_page' => 'LOGIN',
            'payment_method_selected' => $paymentMethodSelected,
            'user_action' => $context->isExpressCheckout() ? 'CONTINUE' : 'PAY_NOW',
            'return_url' => $this->link->getModuleLink('validate'),
            'cancel_url' => $this->link->getModuleLink('cancel'),
        ];

        if ($shippingPreference === 'GET_FROM_FILE' && $context->getCartId()) {
            $data['experience_context']['order_update_callback_config'] = [
                'callback_events' => ['SHIPPING_ADDRESS', 'SHIPPING_OPTIONS'],
                'callback_url' => $this->link->getModuleLink('shipping', ['id_cart' => $context->getCartId()]),
            ];
        }

        if (empty($data)) {
            return [];
        }

        return [
            'payment_source' => [
                'paypal' => $data,
            ],
        ];
    }

    /**
     * @param array<string, mixed> $cart
     *
     * @return array<string, mixed>
     */
    private function buildPayerData(array $cart): array
    {
        $data = [];

        if (!isset($cart['addresses']['invoice'])) {
            $this->logger->warning('Invoice address is missing in the cart.');

            return $data;
        }

        $invoiceAddress = $cart['addresses']['invoice'];
        $countryIsoCode = isset($invoiceAddress->id_country)
            ? $this->countryRepository->getCountryIsoCodeById($invoiceAddress->id_country)
            : '';

        if (isset($invoiceAddress->id_state)) {
            $stateName = $countryIsoCode === 'US' ?
                $this->stateRepository->getIsoById($invoiceAddress->id_state)
                : $this->stateRepository->getNameById($invoiceAddress->id_state);
        } else {
            $stateName = '';
        }

        $data['name'] = [
            'given_name' => isset($invoiceAddress->firstname) ? (string) $invoiceAddress->firstname : '',
            'surname' => isset($invoiceAddress->lastname) ? (string) $invoiceAddress->lastname : '',
        ];

        $data['address'] = OrderPayloadUtility::getAddressPortable(
            $invoiceAddress,
            $countryIsoCode,
            $stateName
        );

        if (isset($cart['customer']->email) && $this->validate->isPayPalEmail($cart['customer']->email)) {
            $data['email_address'] = (string) $cart['customer']->email;
        }

        if (!empty($cart['customer']->birthday) && $cart['customer']->birthday !== '0000-00-00') {
            $data['birth_date'] = (string) $cart['customer']->birthday;
        }

        $cartId = isset($cart['cart']['id']) ? (int) $cart['cart']['id'] : null;
        $parsedPhone = $this->phoneParser->parseFromAddress($invoiceAddress, $countryIsoCode, $cartId);
        if ($parsedPhone !== null) {
            $data['phone'] = [
                'phone_number' => [
                    'national_number' => $parsedPhone->getNationalNumber(),
                ],
                'phone_type' => $this->phoneParser->getPhoneType($parsedPhone),
            ];
        }

        return $data;
    }
}

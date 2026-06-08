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
use PsCheckout\Core\Util\ExperienceContextHelper;
use PsCheckout\Core\Util\PhoneParser;
use PsCheckout\Infrastructure\Adapter\ValidateInterface;
use Psr\Log\LoggerInterface;

class PayPalPaymentSourceNodeBuilder implements PaymentSourceNodeBuilderInterface
{
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

        $cart = $context->getCart();
        $shippingPreference = ExperienceContextHelper::getShippingPreference($cart);
        $urlContext = $this->experienceContextHelper->buildUrlContext();

        $data['experience_context'] = [
            'brand_name' => $this->experienceContextHelper->getBrandName(),
            'shipping_preference' => $shippingPreference,
            'contact_preference' => $context->isExpressCheckout() ? 'UPDATE_CONTACT_INFO' : 'NO_CONTACT_INFO',
            'landing_page' => 'LOGIN',
            'payment_method_selected' => $paymentMethodSelected,
            'user_action' => $context->isExpressCheckout() ? 'CONTINUE' : 'PAY_NOW',
            'return_url' => $urlContext['return_url'],
            'cancel_url' => $urlContext['cancel_url'],
        ];

        if ($shippingPreference === 'GET_FROM_FILE' && $context->getCartId()) {
            $data['experience_context']['order_update_callback_config'] = [
                'callback_events' => ['SHIPPING_ADDRESS', 'SHIPPING_OPTIONS'],
                'callback_url' => $this->experienceContextHelper->buildShippingCallbackUrl($context->getCartId()),
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
        $countryIsoCode = $this->experienceContextHelper->getInvoiceCountryCode($cart);

        $data['name'] = [
            'given_name' => isset($invoiceAddress->firstname) ? (string) $invoiceAddress->firstname : '',
            'surname' => isset($invoiceAddress->lastname) ? (string) $invoiceAddress->lastname : '',
        ];

        $data['address'] = $this->experienceContextHelper->buildInvoicePortableAddress($cart);

        $email = $this->experienceContextHelper->getCustomerEmail($cart);
        if ($email !== '' && $this->validate->isPayPalEmail($email)) {
            $data['email_address'] = $email;
        }

        if (!empty($cart['customer']->birthday) && $cart['customer']->birthday !== '0000-00-00') {
            $data['birth_date'] = (string) $cart['customer']->birthday;
        }

        $cartId = isset($cart['cart']['id']) ? (int) $cart['cart']['id'] : null;
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

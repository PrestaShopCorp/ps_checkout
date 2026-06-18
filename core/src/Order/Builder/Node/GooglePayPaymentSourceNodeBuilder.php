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
use PsCheckout\Core\Settings\Configuration\PayPalConfiguration;
use PsCheckout\Core\Util\ExperienceContextHelper;
use PsCheckout\Core\Util\PhoneParser;
use PsCheckout\Infrastructure\Adapter\ValidateInterface;

class GooglePayPaymentSourceNodeBuilder implements PaymentSourceNodeBuilderInterface
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

    public function supports(string $fundingSource): bool
    {
        return $fundingSource === 'google_pay';
    }

    /**
     * {@inheritDoc}
     */
    public function build(CheckoutContextInterface $context): array
    {
        $data = [];
        $cart = $context->getCart();

        if (isset($cart['addresses']['invoice'])) {
            $invoiceAddress = $cart['addresses']['invoice'];

            $name = $this->experienceContextHelper->getInvoiceName($cart);
            if ($name !== '') {
                $data['name'] = $name;
            }

            $email = $this->experienceContextHelper->getCustomerEmail($cart);
            if ($email !== '' && $this->validate->isPayPalEmail($email)) {
                $data['email_address'] = $email;
            }

            $countryIso = $this->experienceContextHelper->getInvoiceCountryCode($cart);
            $cartId = isset($cart['cart']['id']) ? (int) $cart['cart']['id'] : null;
            $parsedPhone = $this->phoneParser->parseFromAddress($invoiceAddress, $countryIso, $cartId);
            if ($parsedPhone !== null) {
                $data['phone_number'] = [
                    'national_number' => (string) $parsedPhone->getNationalNumber(),
                    'country_code' => (string) $parsedPhone->getCountryCode(),
                ];
            }

            $billingAddress = $this->experienceContextHelper->buildInvoicePortableAddress($cart);
            if (!empty($billingAddress)) {
                $data['card']['billing_address'] = $billingAddress;
            }
        }

        if ($this->payPalConfiguration->is3dSecureEnabled()) {
            $data['attributes']['verification']['method'] = $this->payPalConfiguration->getCardFieldsContingencies();
        }

        $data['experience_context'] = $this->experienceContextHelper->buildUrlContext();

        return ['payment_source' => ['google_pay' => $data]];
    }

    /**
     * {@inheritDoc}
     */
    public function setCart(array $cart)
    {
        $this->cart = $cart;

        return $this;
    }
}

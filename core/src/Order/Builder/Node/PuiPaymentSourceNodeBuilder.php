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

use libphonenumber\PhoneNumberUtil;
use PsCheckout\Core\Exception\PsCheckoutException;
use PsCheckout\Infrastructure\Adapter\ConfigurationInterface;
use PsCheckout\Infrastructure\Adapter\LinkInterface;
use PsCheckout\Infrastructure\Adapter\ValidateInterface;
use PsCheckout\Infrastructure\Repository\CountryRepositoryInterface;
use PsCheckout\Presentation\TranslatorInterface;
use PsCheckout\Utility\Payload\OrderPayloadUtility;
use Psr\Log\LoggerInterface;

class PuiPaymentSourceNodeBuilder implements PuiPaymentSourceNodeBuilderInterface
{
    /**
     * @var array
     */
    private $cart;

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
     * @var null|string
     */
    private $birthDate;

    /**
     * @var null|string
     */
    private $phone;

    /**
     * @var ConfigurationInterface
     */
    private $configuration;

    /**
     * @var LinkInterface
     */
    private $link;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(
        LoggerInterface $logger,
        ValidateInterface $validate,
        CountryRepositoryInterface $countryRepository,
        ConfigurationInterface $configuration,
        LinkInterface $link,
        TranslatorInterface $translator
    ) {
        $this->logger = $logger;
        $this->validate = $validate;
        $this->countryRepository = $countryRepository;
        $this->configuration = $configuration;
        $this->link = $link;
        $this->translator = $translator;
    }

    /**
     * {@inheritDoc}
     */
    public function build(): array
    {
        if (!isset($this->cart['addresses']['invoice'])) {
            $this->logger->warning('Invoice address is missing in the cart for PUI payment.');

            throw new PsCheckoutException('Invoice address is missing in the cart for PUI payment.', PsCheckoutException::CART_ADDRESS_INVOICE_INVALID);
        }

        $invoiceAddress = $this->cart['addresses']['invoice'];
        $customer = $this->cart['customer'] ?? null;

        if (!$customer) {
            $this->logger->warning('Customer is missing in the cart for PUI payment.');

            throw new PsCheckoutException('Customer is missing in the cart for PUI payment.', PsCheckoutException::PRESTASHOP_CONTEXT_INVALID);
        }

        $countryIsoCode = isset($invoiceAddress->id_country)
            ? $this->countryRepository->getCountryIsoCodeById($invoiceAddress->id_country)
            : '';

        $puiData = [];

        $puiData['name'] = [
            'given_name' => isset($invoiceAddress->firstname) ? (string) $invoiceAddress->firstname : '',
            'surname' => isset($invoiceAddress->lastname) ? (string) $invoiceAddress->lastname : '',
        ];

        if (isset($customer->email) && $this->validate->isEmail($customer->email)) {
            $puiData['email'] = (string) $customer->email;
        } else {
            $this->logger->warning('Valid email is required for PUI payment.');

            throw new PsCheckoutException('Valid email is required for PUI payment.', PsCheckoutException::PRESTASHOP_CONTEXT_INVALID);
        }

        $phone = !empty($this->phone)
            ? $this->phone
            : (!empty($invoiceAddress->phone) ? $invoiceAddress->phone : (!empty($invoiceAddress->phone_mobile) ? $invoiceAddress->phone_mobile : ''));

        if (!empty($phone)) {
            try {
                $phoneUtil = PhoneNumberUtil::getInstance();
                $parsedPhone = $phoneUtil->parse($phone, $countryIsoCode);

                if ($phoneUtil->isValidNumber($parsedPhone)) {
                    $puiData['phone'] = [
                        'national_number' => (string) $parsedPhone->getNationalNumber(),
                        'country_code' => (string) $parsedPhone->getCountryCode(),
                    ];
                }
            } catch (\libphonenumber\NumberParseException $exception) {
                $this->logger->warning('Invalid phone number format for PUI payment.', [
                    'id_cart' => isset($this->cart['cart']['id']) ? (int) $this->cart['cart']['id'] : null,
                    'phone' => $phone,
                    'exception' => $exception,
                ]);

                throw $exception;
            } catch (\Exception $exception) {
                $this->logger->warning('Unexpected error formatting phone number for PUI payment.', [
                    'id_cart' => isset($this->cart['cart']['id']) ? (int) $this->cart['cart']['id'] : null,
                    'phone' => $phone,
                    'exception' => $exception,
                ]);

                throw $exception;
            }
        } else {
            $this->logger->warning('Phone number is required for PUI payment.');

            throw new PsCheckoutException('Phone number is required for PUI payment.', PsCheckoutException::CART_ADDRESS_INVOICE_INVALID);
        }

        $billingAddress = OrderPayloadUtility::getAddressPortable(
            $invoiceAddress,
            $countryIsoCode,
            ''
        );

        unset($billingAddress['admin_area_1']);

        $puiData['billing_address'] = $billingAddress;

        if (isset($this->birthDate) && !empty($this->birthDate)) {
            $birthDate = $this->birthDate;

            if (is_string($birthDate) && preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $birthDate, $matches)) {
                $puiData['birth_date'] = $birthDate;
            } else {
                $this->logger->warning('Invalid birth_date format for PUI payment. Expected YYYY-MM-DD.', [
                    'id_cart' => isset($this->cart['cart']['id']) ? (int) $this->cart['cart']['id'] : null,
                    'birth_date' => $birthDate,
                ]);
            }
        }

        $experienceContext = [];

        $customerServicePhone = $this->configuration->get('PS_SHOP_PHONE');
        $customerServiceEmail = $this->configuration->get('PS_SHOP_EMAIL');
        $customerServiceLink = $this->link->getPageLink('contact');
        $contactMethod = $customerServiceLink;

        if (!empty($customerServicePhone)) {
            $contactMethod = $customerServicePhone;
        } elseif (!empty($customerServiceEmail)) {
            $contactMethod = $customerServiceEmail;
        }

        $experienceContext['customer_service_instructions'] = [
            sprintf(
                $this->translator->trans('Contact customer service via %s'),
                $contactMethod
            ),
        ];

        $locale = $this->getLocale();

        if (!empty($locale)) {
            $experienceContext['locale'] = $locale;
        }

        $puiData['experience_context'] = $experienceContext;

        return [
            'payment_source' => [
                'pay_upon_invoice' => $puiData,
            ],
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function setCart(array $cart)
    {
        $this->cart = $cart;

        return $this;
    }

    public function setBirthDate($birthDate): self
    {
        $this->birthDate = $birthDate;

        return $this;
    }

    /**
     * @param ?string $phone
     */
    public function setPhone($phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * @return string
     */
    private function getLocale(): string
    {
        if (isset($this->cart['language']->locale) && !empty($this->cart['language']->locale)) {
            return $this->cart['language']->locale;
        }

        $this->logger->warning('Language locale is missing in the cart for PUI payment.');

        return '';
    }
}

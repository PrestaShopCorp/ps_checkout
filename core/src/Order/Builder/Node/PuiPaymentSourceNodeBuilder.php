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
use PsCheckout\Infrastructure\Adapter\ValidateInterface;
use PsCheckout\Infrastructure\Repository\CountryRepositoryInterface;
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

    public function __construct(
        LoggerInterface $logger,
        ValidateInterface $validate,
        CountryRepositoryInterface $countryRepository
    ) {
        $this->logger = $logger;
        $this->validate = $validate;
        $this->countryRepository = $countryRepository;
    }

    /**
     * {@inheritDoc}
     */
    public function build(): array
    {
        if (!isset($this->cart['addresses']['invoice'])) {
            $this->logger->warning('Invoice address is missing in the cart for PUI payment.');

            return [];
        }

        $invoiceAddress = $this->cart['addresses']['invoice'];
        $customer = $this->cart['customer'] ?? null;

        if (!$customer) {
            $this->logger->warning('Customer is missing in the cart for PUI payment.');

            return [];
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

            return [];
        }

        $phone = !empty($invoiceAddress->phone) ? $invoiceAddress->phone : (!empty($invoiceAddress->phone_mobile) ? $invoiceAddress->phone_mobile : '');

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

                return [];
            } catch (\Exception $exception) {
                $this->logger->warning('Unexpected error formatting phone number for PUI payment.', [
                    'id_cart' => isset($this->cart['cart']['id']) ? (int) $this->cart['cart']['id'] : null,
                    'phone' => $phone,
                    'exception' => $exception,
                ]);

                return [];
            }
        } else {
            $this->logger->warning('Phone number is required for PUI payment.');

            return [];
        }

        $billingAddress = OrderPayloadUtility::getAddressPortable(
            $invoiceAddress,
            $countryIsoCode,
            ''
        );

        unset($billingAddress['admin_area_1']);

        if (empty($billingAddress)) {
            $this->logger->warning('Billing address is required for PUI payment.');

            return [];
        }

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

        if (isset($puiData['phone']['national_number']) && isset($puiData['phone']['country_code'])) {
            $experienceContext['customer_service_instructions'] = [
                sprintf('Customer service phone is +%s %s.', $puiData['phone']['country_code'], $puiData['phone']['national_number']),
            ];
        }

        $locale = $this->getLocale();
        if (!empty($locale)) {
            $experienceContext['locale'] = $locale;
        }

        if (!empty($experienceContext)) {
            $puiData['experience_context'] = $experienceContext;
        }

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

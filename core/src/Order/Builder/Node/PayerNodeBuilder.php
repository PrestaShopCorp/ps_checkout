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

use libphonenumber\PhoneNumberType;
use libphonenumber\PhoneNumberUtil;
use PsCheckout\Infrastructure\Adapter\ValidateInterface;
use PsCheckout\Infrastructure\Repository\CountryRepositoryInterface;
use PsCheckout\Infrastructure\Repository\StateRepositoryInterface;
use PsCheckout\Utility\Payload\OrderPayloadUtility;
use Psr\Log\LoggerInterface;

class PayerNodeBuilder implements PayerNodeBuilderInterface
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
     * @var StateRepositoryInterface
     */
    private $stateRepository;

    public function __construct(
        LoggerInterface $logger,
        ValidateInterface $validate,
        CountryRepositoryInterface $countryRepository,
        StateRepositoryInterface $stateRepository
    ) {
        $this->logger = $logger;
        $this->validate = $validate;
        $this->countryRepository = $countryRepository;
        $this->stateRepository = $stateRepository;
    }

    /**
     * {@inheritDoc}
     */
    public function build()
    {
        $node = [];

        // Ensure address exists before accessing properties
        if (!isset($this->cart['addresses']['invoice'])) {
            $this->logger->warning('Invoice address is missing in the cart.');

            return $node;
        }

        $invoiceAddress = $this->cart['addresses']['invoice'];
        $payerCountryIsoCode = isset($invoiceAddress->id_country)
            ? $this->countryRepository->getCountryIsoCodeById($invoiceAddress->id_country)
            : '';

        $node['payer'] = [
            'name' => [
                'given_name' => isset($invoiceAddress->firstname) ? (string) $invoiceAddress->firstname : '',
                'surname' => isset($invoiceAddress->lastname) ? (string) $invoiceAddress->lastname : '',
            ],
            'address' => OrderPayloadUtility::getAddressPortable(
                $invoiceAddress,
                $payerCountryIsoCode,
                isset($invoiceAddress->id_state) ? $this->stateRepository->getNameById($invoiceAddress->id_state) : ''
            ),
        ];

        // Validate email
        if (isset($this->cart['customer']->email) && $this->validate->isEmail($this->cart['customer']->email)) {
            $node['payer']['email_address'] = (string) $this->cart['customer']->email;
        }

        // Add optional birthdate if provided
        if (!empty($this->cart['customer']->birthday) && $this->cart['customer']->birthday !== '0000-00-00') {
            $node['payer']['birth_date'] = (string) $this->cart['customer']->birthday;
        }

        // Get phone number (prioritize landline, then mobile)
        $phone = !empty($invoiceAddress->phone) ? $invoiceAddress->phone : (!empty($invoiceAddress->phone_mobile) ? $invoiceAddress->phone_mobile : '');

        if (!empty($phone)) {
            try {
                $phoneUtil = PhoneNumberUtil::getInstance();
                $parsedPhone = $phoneUtil->parse($phone, $payerCountryIsoCode);

                if ($phoneUtil->isValidNumber($parsedPhone)) {
                    $node['payer']['phone'] = [
                        'phone_number' => [
                            'national_number' => $parsedPhone->getNationalNumber(),
                        ],
                        'phone_type' => $this->getPhoneType($phoneUtil->getNumberType($parsedPhone)),
                    ];
                }
            } catch (\libphonenumber\NumberParseException $exception) {
                $this->logger->warning('Invalid phone number format.', [
                    'id_cart' => isset($this->cart['cart']['id']) ? (int) $this->cart['cart']['id'] : null,
                    'address_id' => isset($invoiceAddress->id) ? (int) $invoiceAddress->id : null,
                    'phone' => $phone,
                    'exception' => $exception,
                ]);
            } catch (\Exception $exception) {
                $this->logger->warning('Unexpected error formatting phone number.', [
                    'id_cart' => isset($this->cart['cart']['id']) ? (int) $this->cart['cart']['id'] : null,
                    'address_id' => isset($invoiceAddress->id) ? (int) $invoiceAddress->id : null,
                    'phone' => $phone,
                    'exception' => $exception,
                ]);
            }
        }

        return $node;
    }

    /**
     * {@inheritDoc}
     */
    public function setCart(array $cart)
    {
        $this->cart = $cart;

        return $this;
    }

    private function getPhoneType($phoneType)
    {
        switch ($phoneType) {
            case PhoneNumberType::MOBILE:
                return 'MOBILE';
            case PhoneNumberType::PAGER:
                return 'PAGER';
            default:
                return 'OTHER';
        }
    }
}

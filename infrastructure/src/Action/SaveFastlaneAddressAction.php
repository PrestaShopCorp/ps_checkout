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

namespace PsCheckout\Infrastructure\Action;

use Address;
use Country;
use Exception;
use PsCheckout\Infrastructure\Adapter\ContextInterface;
use PsCheckout\Infrastructure\Adapter\CountryInterface;
use PsCheckout\Infrastructure\Adapter\AddressInterface;
use PsCheckout\Infrastructure\Adapter\StateInterface;
use PsCheckout\Presentation\TranslatorInterface;
use Psr\Log\LoggerInterface;
use Validate;

class SaveFastlaneAddressAction implements SaveFastlaneAddressActionInterface
{
    /**
     * @var ContextInterface
     */
    private $context;

    /**
     * @var AddressInterface
     */
    private $address;

    /**
     * @var CountryInterface
     */
    private $country;

    /**
     * @var StateInterface
     */
    private $state;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        ContextInterface $context,
        AddressInterface $address,
        CountryInterface $country,
        StateInterface $state,
        TranslatorInterface $translator,
        LoggerInterface $logger
    ) {
        $this->context = $context;
        $this->address = $address;
        $this->country = $country;
        $this->state = $state;
        $this->translator = $translator;
        $this->logger = $logger;
    }

    /**
     * {@inheritDoc}
     */
    public function execute(int $customerId, array $shippingAddress)
    {
        try {
            $this->address->deleteByCustomerId($customerId);

            $name = $shippingAddress['name'] ?? [];
            $address = $shippingAddress['address'] ?? [];
            $phoneNumber = $shippingAddress['phoneNumber'] ?? [];

            $companyName = trim($shippingAddress['companyName'] ?? '');
            $firstname = trim($name['firstName'] ?? '');
            $lastname = trim($name['lastName'] ?? '');

            $address1 = trim($address['addressLine1'] ?? '');
            $address2 = trim($address['addressLine2'] ?? '');
            $postcode = trim($address['postalCode'] ?? '');
            $state = trim($address['adminArea1'] ?? '');
            $city = trim($address['adminArea2'] ?? '');
            $countryCode = trim($address['countryCode'] ?? '');

            $phone = trim($phoneNumber['countryCode'] ?? '') . trim($phoneNumber['nationalNumber'] ?? '');

            $phone_mobile = $phone;

            $id_country = (int) $this->country->getIdByIsoCode($countryCode);

            $country = new Country($id_country);

            if (!Validate::isLoadedObject($country) || !$country->active) {
                throw new Exception('Invalid or inactive country with ID: ' . $id_country);
            }

            $id_state = 0;

            if (!empty($state) && $id_country) {
                $id_state = $this->state->getIdByIso($state, $id_country);
            }

            $address = new Address();
            $address->alias = $this->translator->trans('My address');
            $address->id_customer = $customerId;
            $address->firstname = $firstname;
            $address->lastname = $lastname;
            $address->company = $companyName;
            $address->address1 = $address1;
            $address->address2 = $address2;
            $address->postcode = $postcode;
            $address->city = $city;
            $address->id_country = $id_country;
            $address->id_state = $id_state;
            $address->phone = $phone;
            $address->phone_mobile = $phone_mobile;

            $address->add();

            $this->context->setContextCartAddresses($address->id);
        } catch (Exception $exception) {
            $this->logger->error(
                sprintf(
                    'SaveFastlaneAddressAction - Exception %s : %s',
                    $exception->getCode(),
                    $exception->getMessage()
                )
            );
        }
    }
}

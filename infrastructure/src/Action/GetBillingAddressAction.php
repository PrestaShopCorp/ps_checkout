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
use PsCheckout\Infrastructure\Adapter\AddressInterface;
use PsCheckout\Infrastructure\Adapter\ContextInterface;
use PsCheckout\Infrastructure\Adapter\CountryInterface;
use PsCheckout\Infrastructure\Adapter\StateInterface;
use Psr\Log\LoggerInterface;
use State;

class GetBillingAddressAction implements GetBillingAddressActionInterface
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
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        ContextInterface $context,
        AddressInterface $address,
        CountryInterface $country,
        StateInterface $state,
        LoggerInterface $logger
    ) {
        $this->context = $context;
        $this->address = $address;
        $this->country = $country;
        $this->state = $state;
        $this->logger = $logger;
    }

    /**
     * {@inheritDoc}
     */
    public function execute(): array
    {
        try {
            $cart = $this->context->getCart();

            if (!$cart || !$cart->id_address_invoice) {
                throw new Exception('Billing address not found in cart');
            }

            $billingAddress = $this->address->initialize((int) $cart->id_address_invoice);

            if (!$billingAddress->id) {
                throw new Exception('Invalid billing address');
            }

            $countryCode = $this->getCountryCode($billingAddress);
            $stateCode = $this->getStateCode($billingAddress);

            return [
                'name' => [
                    'firstName' => $billingAddress->firstname,
                    'lastName' => $billingAddress->lastname,
                    'fullName' => trim($billingAddress->firstname . ' ' . $billingAddress->lastname),
                ],
                'address' => [
                    'addressLine1' => $billingAddress->address1,
                    'addressLine2' => $billingAddress->address2 ?: '',
                    'adminArea2' => $billingAddress->city,
                    'adminArea1' => $stateCode,
                    'postalCode' => $billingAddress->postcode,
                    'countryCode' => $countryCode,
                ],
            ];
        } catch (Exception $exception) {
            $this->logger->error(
                sprintf(
                    'GetBillingAddressAction - Exception %s : %s',
                    $exception->getCode(),
                    $exception->getMessage()
                )
            );

            throw $exception;
        }
    }

    /**
     * Get country ISO code from the billing address
     *
     * @param Address $billingAddress
     *
     * @return string
     */
    private function getCountryCode(Address $billingAddress): string
    {
        try {
            $country = new Country((int) $billingAddress->id_country);

            if ($country->id && $country->iso_code) {
                return strtoupper($country->iso_code);
            }
        } catch (Exception $exception) {
            $this->logger->warning(
                sprintf(
                    'Failed to get country code: %s',
                    $exception->getMessage()
                )
            );
        }

        return '';
    }

    /**
     * Get state ISO code from the billing address
     *
     * @param Address $billingAddress
     *
     * @return string
     */
    private function getStateCode(Address $billingAddress): string
    {
        if (!$billingAddress->id_state) {
            return '';
        }

        try {
            $state = new State((int) $billingAddress->id_state);

            if ($state->id && $state->iso_code) {
                return $state->iso_code;
            }
        } catch (Exception $exception) {
            $this->logger->warning(
                sprintf(
                    'Failed to get state code: %s',
                    $exception->getMessage()
                )
            );
        }

        return '';
    }
}

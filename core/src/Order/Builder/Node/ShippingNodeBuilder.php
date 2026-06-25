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

use Psr\Log\LoggerInterface;
use PsCheckout\Core\Exception\PsCheckoutException;
use PsCheckout\Infrastructure\Repository\CountryRepositoryInterface;
use PsCheckout\Infrastructure\Service\PaypalStateNameResolver;
use PsCheckout\Utility\Payload\OrderPayloadUtility;
use PsCheckout\Utility\Payload\PaypalAddressRequirementsUtility;

class ShippingNodeBuilder implements ShippingNodeBuilderInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var CountryRepositoryInterface
     */
    private $countryRepository;

    /**
     * @var PaypalStateNameResolver
     */
    private $stateNameResolver;

    /**
     * @var array
     */
    private $cart;

    public function __construct(
        LoggerInterface $logger,
        CountryRepositoryInterface $countryRepository,
        PaypalStateNameResolver $stateNameResolver
    ) {
        $this->logger = $logger;
        $this->countryRepository = $countryRepository;
        $this->stateNameResolver = $stateNameResolver;
    }

    /**
     * {@inheritDoc}
     */
    public function build(): array
    {
        if (null === $this->cart || !isset($this->cart['addresses']['shipping'])) {
            throw new PsCheckoutException('Cart data must be set before building order payload');
        }

        if ($this->cart['cart']['is_virtual']) {
            return [];
        }

        $address = $this->cart['addresses']['shipping'];

        $countryIso = $this->countryRepository->getCountryIsoCodeById($address->id_country);

        if (!preg_match('/^[A-Za-z]{2}$/', (string) $countryIso)) {
            $this->logger->warning(
                'ShippingNodeBuilder: invalid country ISO code, shipping address omitted from payload',
                ['id_country' => $address->id_country, 'iso_code' => $countryIso]
            );

            throw new PsCheckoutException(
                'ShippingNodeBuilder: invalid country ISO code',
                PsCheckoutException::CART_SHIPPING_ADDRESS_INVALID
            );
        }

        $stateName = $this->stateNameResolver->resolve($countryIso, (int) $address->id_state);

        $portableAddress = OrderPayloadUtility::getAddressPortable($address, $countryIso, $stateName);

        if (PaypalAddressRequirementsUtility::isCityRequired($countryIso) && empty($portableAddress['admin_area_2'])) {
            $this->logger->warning(
                'ShippingNodeBuilder: city is required but missing for country, shipping address omitted from payload',
                ['id_country' => $address->id_country, 'country_code' => $countryIso]
            );

            throw new PsCheckoutException(
                'ShippingNodeBuilder: city is required but missing',
                PsCheckoutException::CART_SHIPPING_ADDRESS_INVALID
            );
        }

        if (PaypalAddressRequirementsUtility::isPostalCodeRequired($countryIso) && empty($portableAddress['postal_code'])) {
            $this->logger->warning(
                'ShippingNodeBuilder: postal code is required but missing for country, shipping address omitted from payload',
                ['id_country' => $address->id_country, 'country_code' => $countryIso]
            );

            throw new PsCheckoutException(
                'ShippingNodeBuilder: postal code is required but missing',
                PsCheckoutException::CART_SHIPPING_ADDRESS_INVALID
            );
        }

        return [
            'shipping' => [
                'name' => [
                    'full_name' => $address->firstname . ' ' . $address->lastname,
                ],
                'address' => $portableAddress,
            ],
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function setCart(array $cart): self
    {
        $this->cart = $cart;

        return $this;
    }
}

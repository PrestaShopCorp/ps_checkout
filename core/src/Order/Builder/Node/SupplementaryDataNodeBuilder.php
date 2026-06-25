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

use PsCheckout\Core\Exception\PsCheckoutException;
use PsCheckout\Infrastructure\Repository\CountryRepositoryInterface;
use PsCheckout\Infrastructure\Service\PaypalStateNameResolver;
use PsCheckout\Utility\Payload\OrderPayloadUtility;
use PsCheckout\Utility\Payload\PaypalAddressRequirementsUtility;

class SupplementaryDataNodeBuilder implements SupplementaryDataNodeBuilderInterface
{
    /**
     * @var array
     */
    private $cart;

    /**
     * @var CountryRepositoryInterface
     */
    private $countryRepository;

    /**
     * @var PaypalStateNameResolver
     */
    private $stateNameResolver;

    public function __construct(
        CountryRepositoryInterface $countryRepository,
        PaypalStateNameResolver $stateNameResolver
    ) {
        $this->countryRepository = $countryRepository;
        $this->stateNameResolver = $stateNameResolver;
    }

    /**
     * @var array
     */
    private $payload;

    /**
     * {@inheritDoc}
     */
    public function build()
    {
        if (null === $this->cart || null === $this->payload) {
            throw new PsCheckoutException('Cart data and payload must be set before building supplementary data');
        }

        $address = $this->cart['addresses']['invoice'];

        $countryIso = $this->countryRepository->getCountryIsoCodeById($address->id_country);
        $validCountryIso = preg_match('/^[A-Za-z]{2}$/', (string) $countryIso) ? $countryIso : null;

        $stateName = $validCountryIso !== null
            ? $this->stateNameResolver->resolve($validCountryIso, (int) $address->id_state)
            : '';

        $level3 = [
            'shipping_amount' => $this->payload['purchase_units'][0]['amount']['breakdown']['shipping'],
            'duty_amount' => [
                'currency_code' => $this->payload['purchase_units'][0]['amount']['currency_code'],
                'value' => $this->payload['purchase_units'][0]['amount']['value'],
            ],
            'discount_amount' => $this->payload['purchase_units'][0]['amount']['breakdown']['discount'],
            'line_items' => $this->payload['purchase_units'][0]['items'] ?? [],
        ];

        if ($validCountryIso !== null) {
            $portableAddress = OrderPayloadUtility::getAddressPortable($address, $validCountryIso, $stateName);
            $cityMissing = PaypalAddressRequirementsUtility::isCityRequired($validCountryIso) && empty($portableAddress['admin_area_2']);
            $postalMissing = PaypalAddressRequirementsUtility::isPostalCodeRequired($validCountryIso) && empty($portableAddress['postal_code']);
            if (!$cityMissing && !$postalMissing) {
                $level3['shipping_address'] = $portableAddress;
            }
        }

        $node = [
            'supplementary_data' => [
                'card' => [
                    'level_2' => [
                        'tax_total' => $this->payload['purchase_units'][0]['amount']['breakdown']['tax_total'],
                    ],
                    'level_3' => $level3,
                ],
            ],
        ];

        return $node;
    }

    /**
     * {@inheritDoc}
     */
    public function setPayload(array $payload): self
    {
        $this->payload = $payload;

        return $this;
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

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

use PsCheckout\Infrastructure\Repository\CountryRepositoryInterface;
use PsCheckout\Infrastructure\Repository\StateRepositoryInterface;
use PsCheckout\Utility\Payload\OrderPayloadUtility;

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
     * @var StateRepositoryInterface
     */
    private $stateRepository;

    public function __construct(
        CountryRepositoryInterface $countryRepository,
        StateRepositoryInterface $stateRepository
    ) {
        $this->countryRepository = $countryRepository;
        $this->stateRepository = $stateRepository;
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
        $address = $this->cart['addresses']['invoice'];

        $countryIso = $this->countryRepository->getCountryIsoCodeById($address->id_country);
        $stateName = $this->stateRepository->getNameById($address->id_state);

        $node = [
            'supplementary_data' => [
                'card' => [
                    'level_2' => [
                        'tax_total' => $this->payload['amount']['breakdown']['tax_total'],
                    ],
                    'level_3' => [
                        'shipping_amount' => $this->payload['amount']['breakdown']['shipping'],
                        'duty_amount' => [
                            'currency_code' => $this->payload['amount']['currency_code'],
                            'value' => $this->payload['amount']['value'],
                        ],
                        'discount_amount' => $this->payload['amount']['breakdown']['discount'],
                        'shipping_address' => OrderPayloadUtility::getAddressPortable($address, $countryIso, $stateName),
                        'line_items' => $this->payload['items'],
                    ],
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

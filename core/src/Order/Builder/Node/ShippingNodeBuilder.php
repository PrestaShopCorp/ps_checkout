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
use PsCheckout\Infrastructure\Repository\GenderRepositoryInterface;
use PsCheckout\Infrastructure\Repository\StateRepositoryInterface;
use PsCheckout\Utility\Payload\OrderPayloadUtility;

class ShippingNodeBuilder implements ShippingNodeBuilderInterface
{
    /**
     * @var GenderRepositoryInterface
     */
    private $gender;

    /**
     * @var CountryRepositoryInterface
     */
    private $countryRepository;

    /**
     * @var StateRepositoryInterface
     */
    private $stateRepository;

    /**
     * @var array
     */
    private $cart;

    public function __construct(
        GenderRepositoryInterface $gender,
        CountryRepositoryInterface $countryRepository,
        StateRepositoryInterface $stateRepository
    ) {
        $this->gender = $gender;
        $this->countryRepository = $countryRepository;
        $this->stateRepository = $stateRepository;
    }

    /**
     * {@inheritDoc}
     */
    public function build(): array
    {
        if (null === $this->cart || !isset($this->cart['addresses']['shipping'])) {
            throw new PsCheckoutException('Cart data must be set before building order payload');
        }

        $address = $this->cart['addresses']['shipping'];

        $countryIso = $this->countryRepository->getCountryIsoCodeById($address->id_country);
        $stateName = $this->stateRepository->getNameById($address->id_state);

        return [
            'shipping' => [
                'name' => [
                    'full_name' => $this->gender->getGenderNameById($this->cart['customer']->id_gender, $this->cart['language']->id) . ' ' . $this->cart['addresses']['shipping']->lastname . ' ' . $this->cart['addresses']['shipping']->firstname,
                ],
                'address' => OrderPayloadUtility::getAddressPortable($address, $countryIso, $stateName),
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

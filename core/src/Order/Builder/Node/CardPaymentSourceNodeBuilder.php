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

use PsCheckout\Core\Settings\Configuration\PayPalConfiguration;
use PsCheckout\Infrastructure\Repository\CountryRepositoryInterface;
use PsCheckout\Infrastructure\Repository\StateRepositoryInterface;
use PsCheckout\Utility\Payload\OrderPayloadUtility;

class CardPaymentSourceNodeBuilder implements CardPaymentSourceNodeBuilderInterface
{
    /**
     * @var array
     */
    private $cart;

    /**
     * @var string
     */
    private $paypalVaultId;

    /**
     * @var string
     */
    private $paypalCustomerId;

    /**
     * @var bool
     */
    private $savePaymentMethod;

    /**
     * @var PayPalConfiguration
     */
    private $paypalConfiguration;

    /**
     * @var CountryRepositoryInterface
     */
    private $countryRepository;

    /**
     * @var StateRepositoryInterface
     */
    private $stateRepository;

    public function __construct(
        PayPalConfiguration $paypalConfiguration,
        CountryRepositoryInterface $countryRepository,
        StateRepositoryInterface $stateRepository
    ) {
        $this->paypalConfiguration = $paypalConfiguration;
        $this->countryRepository = $countryRepository;
        $this->stateRepository = $stateRepository;
    }

    /**
     * {@inheritDoc}
     */
    public function build(): array
    {
        $address = $this->cart['addresses']['invoice'];

        $countryIso = $this->countryRepository->getCountryIsoCodeById($address->id_country);
        $stateName = $this->stateRepository->getNameById($address->id_state);

        $node = [
            'payment_source' => [
                'card' => [
                    'name' => $this->cart['addresses']['invoice']->firstname . ' ' . $this->cart['addresses']['invoice']->lastname,
                    'billing_address' => OrderPayloadUtility::getAddressPortable($address, $countryIso, $stateName),
                ],
            ],
        ];

        if ($this->paypalConfiguration->is3dSecureEnabled()) {
            $node['payment_source']['card']['attributes']['verification']['method'] = $this->paypalConfiguration->getCardFieldsContingencies();
        }

        if ($this->paypalVaultId) {
            unset($node['payment_source']['card']['billing_address']);
            $node['payment_source']['card']['vault_id'] = $this->paypalVaultId;
        }

        if ($this->paypalCustomerId) {
            $node['payment_source']['card']['attributes']['customer'] = [
                'id' => $this->paypalCustomerId,
            ];
        }

        if ($this->savePaymentMethod) {
            $node['payment_source']['card']['attributes']['vault'] = [
                'store_in_vault' => 'ON_SUCCESS',
            ];
        }

        return $node;
    }

    /**
     * {@inheritDoc}
     */
    public function setCart(array $cart): self
    {
        $this->cart = $cart;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setPaypalVaultId($paypalVaultId): self
    {
        $this->paypalVaultId = $paypalVaultId;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setPaypalCustomerId($paypalCustomerId): self
    {
        $this->paypalCustomerId = $paypalCustomerId;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setSavePaymentMethod(bool $savePaymentMethod): self
    {
        $this->savePaymentMethod = $savePaymentMethod;

        return $this;
    }
}

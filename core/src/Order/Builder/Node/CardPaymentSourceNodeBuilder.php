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

use PsCheckout\Core\Order\Builder\CheckoutContextInterface;
use PsCheckout\Core\Order\Builder\PaymentSourceNodeBuilderInterface;
use PsCheckout\Core\Settings\Configuration\PayPalConfiguration;
use PsCheckout\Infrastructure\Adapter\LinkInterface;
use PsCheckout\Infrastructure\Repository\CountryRepositoryInterface;
use PsCheckout\Infrastructure\Repository\StateRepositoryInterface;
use PsCheckout\Utility\Payload\OrderPayloadUtility;

class CardPaymentSourceNodeBuilder implements PaymentSourceNodeBuilderInterface
{
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

    /**
     * @var LinkInterface
     */
    private $link;

    public function __construct(
        PayPalConfiguration $paypalConfiguration,
        CountryRepositoryInterface $countryRepository,
        StateRepositoryInterface $stateRepository,
        LinkInterface $link
    ) {
        $this->paypalConfiguration = $paypalConfiguration;
        $this->countryRepository = $countryRepository;
        $this->stateRepository = $stateRepository;
        $this->link = $link;
    }

    public function supports(string $fundingSource): bool
    {
        return $fundingSource === 'card';
    }

    /**
     * {@inheritDoc}
     */
    public function build(CheckoutContextInterface $context): array
    {
        $cart = $context->getCart();
        $address = $cart['addresses']['invoice'];

        $countryIso = $this->countryRepository->getCountryIsoCodeById($address->id_country);
        $stateName = $countryIso === 'US' ?
            $this->stateRepository->getIsoById($address->id_state)
            : $this->stateRepository->getNameById($address->id_state);

        $node = [
            'payment_source' => [
                'card' => [
                    'name' => $cart['addresses']['invoice']->firstname . ' ' . $cart['addresses']['invoice']->lastname,
                    'billing_address' => OrderPayloadUtility::getAddressPortable($address, $countryIso, $stateName),
                ],
            ],
        ];

        if ($this->paypalConfiguration->is3dSecureEnabled()) {
            $node['payment_source']['card']['attributes']['verification']['method'] = $this->paypalConfiguration->getCardFieldsContingencies();
        }

        if ($context->getPaypalVaultId()) {
            unset($node['payment_source']['card']['billing_address']);
            $node['payment_source']['card']['vault_id'] = $context->getPaypalVaultId();
        }

        if ($context->getPaypalCustomerId()) {
            $node['payment_source']['card']['attributes']['customer'] = [
                'id' => $context->getPaypalCustomerId(),
            ];
        }

        if ($context->isSavePaymentMethod()) {
            $node['payment_source']['card']['attributes']['vault'] = [
                'store_in_vault' => 'ON_SUCCESS',
            ];
        }

        if ($context->getPaypalVaultId()) {
            $node['payment_source']['card']['stored_credential'] = [
                'payment_initiator' => 'CUSTOMER',
                'payment_type' => 'UNSCHEDULED',
                'usage' => 'SUBSEQUENT',
            ];
        } elseif ($context->isSavePaymentMethod()) {
            $node['payment_source']['card']['stored_credential'] = [
                'payment_initiator' => 'CUSTOMER',
                'payment_type' => 'UNSCHEDULED',
                'usage' => 'FIRST',
            ];
        }

        $node['payment_source']['card']['experience_context'] = [
            'return_url' => $this->link->getModuleLink('validate'),
            'cancel_url' => $this->link->getModuleLink('cancel'),
        ];

        return $node;
    }
}

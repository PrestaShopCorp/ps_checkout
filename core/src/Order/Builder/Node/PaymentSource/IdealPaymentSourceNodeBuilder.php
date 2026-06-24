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

namespace PsCheckout\Core\Order\Builder\Node\PaymentSource;

use PsCheckout\Infrastructure\Adapter\ConfigurationInterface;
use PsCheckout\Infrastructure\Adapter\LinkInterface;
use PsCheckout\Infrastructure\Repository\CountryRepositoryInterface;
use PsCheckout\Utility\Common\StringUtility;

class IdealPaymentSourceNodeBuilder implements ApmPaymentSourceNodeBuilderInterface
{
    /**
     * @var ConfigurationInterface
     */
    private $configuration;

    /**
     * @var LinkInterface
     */
    private $link;

    /**
     * @var CountryRepositoryInterface
     */
    private $countryRepository;

    /**
     * @var array<string, mixed>
     */
    private $cart;

    public function __construct(
        ConfigurationInterface $configuration,
        LinkInterface $link,
        CountryRepositoryInterface $countryRepository
    ) {
        $this->configuration = $configuration;
        $this->link = $link;
        $this->countryRepository = $countryRepository;
    }

    /**
     * {@inheritDoc}
     */
    public function build(): array
    {
        $invoiceAddress = isset($this->cart['addresses']['invoice']) ? $this->cart['addresses']['invoice'] : null;
        $firstName = isset($invoiceAddress->firstname) ? (string) $invoiceAddress->firstname : '';
        $lastName = isset($invoiceAddress->lastname) ? (string) $invoiceAddress->lastname : '';
        $countryCode = isset($invoiceAddress->id_country)
            ? $this->countryRepository->getCountryIsoCodeById($invoiceAddress->id_country)
            : '';

        return [
            'payment_source' => [
                'ideal' => [
                    'name' => trim($firstName . ' ' . $lastName),
                    'country_code' => $countryCode,
                    'experience_context' => [
                        'brand_name' => StringUtility::normalizeBrandName((string) $this->configuration->get('PS_SHOP_NAME')),
                        'return_url' => $this->link->getModuleLink('validate'),
                        'cancel_url' => $this->link->getModuleLink('cancel'),
                    ],
                ],
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
}

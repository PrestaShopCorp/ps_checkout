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

namespace PsCheckout\Presentation\Presenter\Cart;

use PsCheckout\Infrastructure\Adapter\AddressInterface;
use PsCheckout\Infrastructure\Adapter\ContextInterface;
use PsCheckout\Infrastructure\Adapter\CurrencyInterface;
use PsCheckout\Infrastructure\Repository\CustomerRepositoryInterface;
use PsCheckout\Infrastructure\Repository\LanguageRepository;
use PsCheckout\Infrastructure\Repository\LanguageRepositoryInterface;
use PsCheckout\Presentation\Presenter\PresenterInterface;

class CartPresenter implements PresenterInterface
{
    /**
     * @var ContextInterface|null
     */
    private $context;

    /**
     * @var AddressInterface
     */
    private $address;

    /**
     * @var CurrencyInterface
     */
    private $currency;

    /**
     * @var LanguageRepository
     */
    private $languageRepository;

    /**
     * @var LanguageRepository
     */
    private $customerRepository;

    public function __construct(
        ContextInterface $context,
        AddressInterface $address,
        CurrencyInterface $currency,
        LanguageRepositoryInterface $languageRepository,
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->context = $context;
        $this->address = $address;
        $this->currency = $currency;
        $this->languageRepository = $languageRepository;
        $this->customerRepository = $customerRepository;
    }

    /**
     * {@inheritDoc}
     */
    public function present(): array
    {
        $productList = $this->context->getCart()->getProducts();
        $shippingAddress = $this->address->initialize((int) $this->context->getCart()->id_address_delivery);
        $invoiceAddress = $this->address->initialize((int) $this->context->getCart()->id_address_invoice);
        $currency = $this->currency->getCurrencyInstance((int) $this->context->getCart()->id_currency);

        return [
            'cart' => [
                'id' => $this->context->getCart()->id,
                'shipping_cost' => $this->context->getCart()->getTotalShippingCost(null, true),
                'totals' => [
                    'total_including_tax' => [
                        'amount' => $this->context->getCart()->getOrderTotal(true),
                    ],
                ],
                'subtotals' => [
                    'gift_wrapping' => [
                        'amount' => $this->context->getCart()->getGiftWrappingPrice(true),
                    ],
                ],
            ],
            'customer' => $this->customerRepository->getOneBy(['id_customer' => (int) $this->context->getCart()->id_customer]),
            'language' => $this->languageRepository->getOneBy(['id_lang' => (int) $this->context->getCart()->id_lang]),
            'products' => $productList,
            'addresses' => [
                'shipping' => $shippingAddress,
                'invoice' => $invoiceAddress,
            ],
            'currency' => [
                'iso_code' => $currency->iso_code,
            ],
        ];
    }
}

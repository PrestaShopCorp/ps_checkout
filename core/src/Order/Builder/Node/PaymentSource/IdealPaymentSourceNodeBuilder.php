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

use PsCheckout\Core\Util\ExperienceContextHelper;

class IdealPaymentSourceNodeBuilder implements ApmPaymentSourceNodeBuilderInterface
{
    /**
     * @var ExperienceContextHelper
     */
    private $experienceContextHelper;

    /**
     * @var array<string, mixed>
     */
    private $cart;

    public function __construct(ExperienceContextHelper $experienceContextHelper)
    {
        $this->experienceContextHelper = $experienceContextHelper;
    }

    /**
     * {@inheritDoc}
     */
    public function build(): array
    {
        return [
            'payment_source' => [
                'ideal' => [
                    'name' => $this->experienceContextHelper->getInvoiceName($this->cart),
                    'country_code' => $this->experienceContextHelper->getInvoiceCountryCode($this->cart),
                    'experience_context' => $this->experienceContextHelper->buildBaseContext($this->cart),
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

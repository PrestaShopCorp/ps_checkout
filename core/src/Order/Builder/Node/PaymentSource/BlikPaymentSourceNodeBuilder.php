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

use PsCheckout\Core\Order\Builder\CheckoutContextInterface;
use PsCheckout\Core\Order\Builder\PaymentSourceNodeBuilderInterface;
use PsCheckout\Core\Util\ExperienceContextHelper;
use PsCheckout\Infrastructure\Adapter\ValidateInterface;

class BlikPaymentSourceNodeBuilder implements PaymentSourceNodeBuilderInterface
{
    /**
     * @var ExperienceContextHelper
     */
    private $experienceContextHelper;

    /**
     * @var ValidateInterface
     */
    private $validate;

    public function __construct(
        ExperienceContextHelper $experienceContextHelper,
        ValidateInterface $validate
    ) {
        $this->experienceContextHelper = $experienceContextHelper;
        $this->validate = $validate;
    }

    public function supports(string $fundingSource): bool
    {
        return $fundingSource === 'blik';
    }

    /**
     * {@inheritDoc}
     */
    public function build(CheckoutContextInterface $context): array
    {
        $cart = $context->getCart();

        $data = [
            'name' => $this->experienceContextHelper->getInvoiceName($cart),
            'country_code' => $this->experienceContextHelper->getInvoiceCountryCode($cart),
            'experience_context' => $this->experienceContextHelper->buildBaseContext($cart),
        ];

        $email = $this->experienceContextHelper->getCustomerEmail($cart);
        if ($email !== '' && $this->validate->isPayPalEmail($email)) {
            $data['email'] = $email;
        }

        return [
            'payment_source' => [
                'blik' => $data,
            ],
        ];
    }
}

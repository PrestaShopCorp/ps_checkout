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

use PsCheckout\Core\Exception\PsCheckoutException;
use PsCheckout\Core\Util\ExperienceContextHelper;
use PsCheckout\Infrastructure\Adapter\ValidateInterface;
use Psr\Log\LoggerInterface;

class P24PaymentSourceNodeBuilder implements ApmPaymentSourceNodeBuilderInterface
{
    /**
     * @var ExperienceContextHelper
     */
    private $experienceContextHelper;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ValidateInterface
     */
    private $validate;

    /**
     * @var array<string, mixed>
     */
    private $cart;

    public function __construct(
        ExperienceContextHelper $experienceContextHelper,
        LoggerInterface $logger,
        ValidateInterface $validate
    ) {
        $this->experienceContextHelper = $experienceContextHelper;
        $this->logger = $logger;
        $this->validate = $validate;
    }

    /**
     * {@inheritDoc}
     *
     * @throws PsCheckoutException When no valid email is available for P24 (required by PayPal)
     */
    public function build(): array
    {
        $email = $this->experienceContextHelper->getCustomerEmail($this->cart);

        if (!$this->validate->isPayPalEmail($email)) {
            $this->logger->warning('Valid email is required for P24 payment.');

            throw new PsCheckoutException('Valid email is required for P24 payment.', PsCheckoutException::CART_CUSTOMER_EMAIL_INVALID);
        }

        return [
            'payment_source' => [
                'p24' => [
                    'name' => $this->experienceContextHelper->getInvoiceName($this->cart),
                    'email' => $email,
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

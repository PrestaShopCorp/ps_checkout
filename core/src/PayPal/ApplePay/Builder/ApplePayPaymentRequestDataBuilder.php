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

namespace PsCheckout\Core\PayPal\ApplePay\Builder;

use PsCheckout\Core\Order\Builder\CheckoutContextBuilderInterface;
use PsCheckout\Core\PayPal\ApplePay\ValueObject\ApplePayPaymentRequestData;

class ApplePayPaymentRequestDataBuilder implements ApplePayPaymentRequestDataBuilderInterface
{
    /**
     * @var CheckoutContextBuilderInterface
     */
    private $checkoutContextBuilder;

    /**
     * @var ApplePayNodeBuilderInterface
     */
    private $amountBuilder;

    /**
     * @var ApplePayNodeBuilderInterface
     */
    private $contactBuilder;

    /**
     * @var ApplePayNodeBuilderInterface
     */
    private $shippingBuilder;

    /**
     * @var ApplePayNodeBuilderInterface
     */
    private $couponBuilder;

    /**
     * @var ApplePayNodeBuilderInterface
     */
    private $applicationDataBuilder;

    public function __construct(
        CheckoutContextBuilderInterface $checkoutContextBuilder,
        ApplePayNodeBuilderInterface $amountBuilder,
        ApplePayNodeBuilderInterface $contactBuilder,
        ApplePayNodeBuilderInterface $shippingBuilder,
        ApplePayNodeBuilderInterface $couponBuilder,
        ApplePayNodeBuilderInterface $applicationDataBuilder
    ) {
        $this->checkoutContextBuilder = $checkoutContextBuilder;
        $this->amountBuilder = $amountBuilder;
        $this->contactBuilder = $contactBuilder;
        $this->shippingBuilder = $shippingBuilder;
        $this->couponBuilder = $couponBuilder;
        $this->applicationDataBuilder = $applicationDataBuilder;
    }

    /**
     * {@inheritDoc}
     */
    public function build(): ApplePayPaymentRequestData
    {
        $context = $this->checkoutContextBuilder
            ->setFundingSource('applepay')
            ->build();

        $data = array_merge(
            $this->amountBuilder->build($context),
            $this->contactBuilder->build($context),
            $this->shippingBuilder->build($context),
            $this->couponBuilder->build($context),
            $this->applicationDataBuilder->build($context)
        );

        return new ApplePayPaymentRequestData($data);
    }
}

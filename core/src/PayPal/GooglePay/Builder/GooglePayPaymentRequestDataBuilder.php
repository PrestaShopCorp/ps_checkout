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

namespace PsCheckout\Core\PayPal\GooglePay\Builder;

use PsCheckout\Core\Order\Builder\CheckoutContextBuilderInterface;
use PsCheckout\Core\Order\Builder\OrderPayloadBuilderInterface;
use PsCheckout\Core\PayPal\GooglePay\ValueObject\GooglePayPaymentRequestData;
use PsCheckout\Presentation\TranslatorInterface;

class GooglePayPaymentRequestDataBuilder implements GooglePayPaymentRequestDataBuilderInterface
{
    /**
     * @var OrderPayloadBuilderInterface
     */
    private $orderPayloadBuilder;

    /**
     * @var CheckoutContextBuilderInterface
     */
    private $checkoutContextBuilder;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(
        OrderPayloadBuilderInterface $orderPayloadBuilder,
        CheckoutContextBuilderInterface $checkoutContextBuilder,
        TranslatorInterface $translator
    ) {
        $this->orderPayloadBuilder = $orderPayloadBuilder;
        $this->checkoutContextBuilder = $checkoutContextBuilder;
        $this->translator = $translator;
    }

    /**
     * {@inheritDoc}
     */
    public function build(int $cartId): GooglePayPaymentRequestData
    {
        $context = $this->checkoutContextBuilder
            ->setFundingSource('googlepay')
            ->build();

        $payload = $this->orderPayloadBuilder->build($context);

        return new GooglePayPaymentRequestData(
            $payload['purchase_units'][0]['amount']['currency_code'],
            $payload['purchase_units'][0]['amount']['value'],
            $this->translator->trans('Total'),
            $payload['application_context']['brand_name']
        );
    }
}

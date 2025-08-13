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

use PsCheckout\Core\Order\Builder\OrderPayloadBuilderInterface;
use PsCheckout\Core\PayPal\GooglePay\ValueObject\GooglePayPaymentRequestData;
use PsCheckout\Presentation\Presenter\PresenterInterface;
use PsCheckout\Presentation\TranslatorInterface;

class GooglePayPaymentRequestDataBuilder implements GooglePayPaymentRequestDataBuilderInterface
{
    /**
     * @var OrderPayloadBuilderInterface
     */
    private $orderPayloadBuilder;

    /**
     * @var PresenterInterface
     */
    private $cartPresenter;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(
        OrderPayloadBuilderInterface $orderPayloadBuilder,
        PresenterInterface $cartPresenter,
        TranslatorInterface $translator
    ) {
        $this->orderPayloadBuilder = $orderPayloadBuilder;
        $this->cartPresenter = $cartPresenter;
        $this->translator = $translator;
    }

    /**
     * {@inheritDoc}
     */
    public function build(int $cartId): GooglePayPaymentRequestData
    {
        $this->orderPayloadBuilder
            ->setCart($this->cartPresenter->present());

        $payload = $this->orderPayloadBuilder->build();

        return new GooglePayPaymentRequestData(
            $payload['amount']['currency_code'],
            $payload['amount']['value'],
            $this->translator->trans('Total GooglePay'),
            $payload['application_context']['brand_name']
        );
    }
}

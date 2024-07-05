<?php
namespace PrestaShop\Module\PrestashopCheckout\PayPal\GooglePay\Query;

use PrestaShop\Module\PrestashopCheckout\Builder\Payload\OrderPayloadBuilder;
use PrestaShop\Module\PrestashopCheckout\PayPal\GooglePay\Builder\GoogleTransactionInfoBuilder;
use PrestaShop\Module\PrestashopCheckout\Presenter\Cart\CartPresenter;

class GetGooglePayTransactionInfoQueryHandler
{
    /**
     * @var GoogleTransactionInfoBuilder
     */
    private $builder;

    public function __construct(GoogleTransactionInfoBuilder $builder)
    {
        $this->builder = $builder;
    }

    public function handle(GetGooglePayTransactionInfoQuery $query)
    {
        $cartPresenter = (new CartPresenter())->present();
        $orderPayloadBuilder = new OrderPayloadBuilder($cartPresenter);

        $orderPayloadBuilder->buildFullPayload();
        $payload = $orderPayloadBuilder->presentPayload();

        return new GetGooglePayTransactionInfoQueryResult($this->builder->buildFromPayPalPayload($payload->getArray()));
    }
}

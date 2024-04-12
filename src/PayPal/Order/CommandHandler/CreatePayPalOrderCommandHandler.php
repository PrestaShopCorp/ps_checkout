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

namespace PrestaShop\Module\PrestashopCheckout\PayPal\Order\CommandHandler;

use PrestaShop\Module\PrestashopCheckout\Builder\Payload\OrderPayloadBuilder;
use PrestaShop\Module\PrestashopCheckout\Event\EventDispatcherInterface;
use PrestaShop\Module\PrestashopCheckout\Exception\PayPalException;
use PrestaShop\Module\PrestashopCheckout\Exception\PsCheckoutException;
use PrestaShop\Module\PrestashopCheckout\Http\CheckoutHttpClient;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Command\CreatePayPalOrderCommand;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Event\PayPalOrderCreatedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Exception\PayPalOrderException;
use PrestaShop\Module\PrestashopCheckout\Presenter\Cart\CartPresenter;
use PrestaShop\Module\PrestashopCheckout\ShopContext;

class CreatePayPalOrderCommandHandler
{
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var CheckoutHttpClient
     */
    private $httpClient;
    /**
     * @var ShopContext
     */
    private $shopContext;

    public function __construct(
        CheckoutHttpClient $httpClient,
        EventDispatcherInterface $eventDispatcher,
        ShopContext $shopContext
    ) {
        $this->httpClient = $httpClient;
        $this->eventDispatcher = $eventDispatcher;
        $this->shopContext = $shopContext;
    }

    /**
     * @param CreatePayPalOrderCommand $command
     *
     * @return void
     *
     * @throws PayPalException
     * @throws PayPalOrderException
     * @throws PsCheckoutException
     */
    public function handle(CreatePayPalOrderCommand $command)
    {
        $cartPresenter = (new CartPresenter())->present();
        $builder = new OrderPayloadBuilder($cartPresenter);
        $builder->setIsCard($command->getFundingSource() === 'card' && $command->isHostedFields());
        $builder->setExpressCheckout($command->isExpressCheckout());

        if ($this->shopContext->isShop17()) {
            // Build full payload in 1.7
            $builder->buildFullPayload();
        } else {
            // if on 1.6 always build minimal payload
            $builder->buildMinimalPayload();
        }

        $response = $this->httpClient->createOrder($builder->presentPayload()->getArray());
        $order = json_decode($response->getBody(), true);
        $this->eventDispatcher->dispatch(new PayPalOrderCreatedEvent(
            $order['id'],
            $order,
            $command->getCartId()->getValue(),
            $command->isHostedFields(),
            $command->isExpressCheckout(),
            $command->getFundingSource()
        ));
    }
}

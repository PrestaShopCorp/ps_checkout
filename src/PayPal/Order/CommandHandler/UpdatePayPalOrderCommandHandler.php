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

use Exception;
use PrestaShop\Module\PrestashopCheckout\Builder\Payload\OrderPayloadBuilder;
use PrestaShop\Module\PrestashopCheckout\Cart\Exception\CartException;
use PrestaShop\Module\PrestashopCheckout\Event\EventDispatcherInterface;
use PrestaShop\Module\PrestashopCheckout\Exception\PayPalException;
use PrestaShop\Module\PrestashopCheckout\Exception\PsCheckoutException;
use PrestaShop\Module\PrestashopCheckout\Http\CheckoutHttpClient;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Command\UpdatePayPalOrderCommand;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Event\PayPalOrderUpdatedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Exception\PayPalOrderException;
use PrestaShop\Module\PrestashopCheckout\Presenter\Cart\CartPresenter;
use PrestaShop\Module\PrestashopCheckout\ShopContext;

class UpdatePayPalOrderCommandHandler
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

    /**
     * @param CheckoutHttpClient $httpClient
     * @param EventDispatcherInterface $eventDispatcher
     * @param ShopContext $shopContext
     */
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
     * @param UpdatePayPalOrderCommand $command
     *
     * @return void
     *
     * @throws CartException|PayPalException|PayPalOrderException|PsCheckoutException|Exception
     */
    public function handle(UpdatePayPalOrderCommand $command)
    {
        $cartPresenter = (new CartPresenter())->present();
        $builder = new OrderPayloadBuilder($cartPresenter, true);
        $builder->setIsUpdate(true);
        $builder->setPaypalOrderId($command->getPayPalOrderId()->getValue());
        $builder->setIsCard($command->getFundingSource() === 'card' && $command->isHostedFields());
        $builder->setExpressCheckout($command->isExpressCheckout());

        if ($this->shopContext->isShop17()) {
            // Build full payload in 1.7
            $builder->buildFullPayload();
        } else {
            // if on 1.6 always build minimal payload
            $builder->buildMinimalPayload();
        }

        $response = $this->httpClient->updateOrder($builder->presentPayload()->getArray());
        $order = json_decode($response->getBody(), true);

        $this->eventDispatcher->dispatch(new PayPalOrderUpdatedEvent(
            $order['id'],
            $order,
            $command->getCartId()->getValue(),
            $command->isHostedFields(),
            $command->isExpressCheckout(),
            $command->getFundingSource()
        ));
    }
}

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

use PrestaShop\Module\PrestashopCheckout\Cart\CartRepositoryInterface;
use PrestaShop\Module\PrestashopCheckout\Cart\Exception\CartNotFoundException;
use PrestaShop\Module\PrestashopCheckout\Event\EventDispatcherInterface;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Command\CreatePayPalOrderCommand;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\CreatePayPalOrderPayloadBuilderInterface;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Event\PayPalOrderCreatedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Exception\PayPalOrderException;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\PayPalOrderHttpClientInterface;

class CreatePayPalOrderCommandHandler
{
    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var CreatePayPalOrderPayloadBuilderInterface
     */
    private $createPayPalOrderPayloadBuilder;

    /**
     * @var PayPalOrderHttpClientInterface
     */
    private $orderHttpClient;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @param CartRepositoryInterface $cartRepository
     * @param CreatePayPalOrderPayloadBuilderInterface $createPayPalOrderPayloadBuilder
     * @param PayPalOrderHttpClientInterface $orderHttpClient
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        CartRepositoryInterface $cartRepository,
        CreatePayPalOrderPayloadBuilderInterface $createPayPalOrderPayloadBuilder,
        PayPalOrderHttpClientInterface $orderHttpClient,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->cartRepository = $cartRepository;
        $this->createPayPalOrderPayloadBuilder = $createPayPalOrderPayloadBuilder;
        $this->orderHttpClient = $orderHttpClient;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param CreatePayPalOrderCommand $command
     *
     * @return void
     *
     * @throws PayPalOrderException
     * @throws CartNotFoundException
     */
    public function handle(CreatePayPalOrderCommand $command)
    {
        $cart = $this->cartRepository->getCartById($command->getCartId());
        $payload = $this->createPayPalOrderPayloadBuilder->build($cart, $command->getFundingSource());
        $order = $this->orderHttpClient->createOrder($payload);
        $this->eventDispatcher->dispatch(new PayPalOrderCreatedEvent($order->getId(), $order->toArray()));
    }
}

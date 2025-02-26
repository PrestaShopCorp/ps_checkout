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

namespace PrestaShop\Module\PrestashopCheckout\Checkout\EventSubscriber;

use PrestaShop\Module\PrestashopCheckout\Cart\Exception\CartException;
use PrestaShop\Module\PrestashopCheckout\Checkout\CheckoutChecker;
use PrestaShop\Module\PrestashopCheckout\Checkout\Command\UpdatePaymentMethodSelectedCommand;
use PrestaShop\Module\PrestashopCheckout\Checkout\CommandHandler\UpdatePaymentMethodSelectedCommandHandler;
use PrestaShop\Module\PrestashopCheckout\Checkout\Event\CheckoutCompletedEvent;
use PrestaShop\Module\PrestashopCheckout\CommandBus\QueryBusInterface;
use PrestaShop\Module\PrestashopCheckout\Exception\HttpTimeoutException;
use PrestaShop\Module\PrestashopCheckout\Exception\PayPalException;
use PrestaShop\Module\PrestashopCheckout\Exception\PsCheckoutException;
use PrestaShop\Module\PrestashopCheckout\Order\Command\CreateOrderCommand;
use PrestaShop\Module\PrestashopCheckout\Order\CommandHandler\CreateOrderCommandHandler;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Command\CapturePayPalOrderCommand;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\CommandHandler\CapturePayPalOrderCommandHandler;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Exception\PayPalOrderException;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Query\GetPayPalOrderForCheckoutCompletedQuery;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Query\GetPayPalOrderForCheckoutCompletedQueryResult;
use PrestaShop\Module\PrestashopCheckout\Repository\PsCheckoutCartRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CheckoutEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private QueryBusInterface $queryBus,
        private CheckoutChecker $checkoutChecker,
        private PsCheckoutCartRepository $psCheckoutCartRepository,
        private UpdatePaymentMethodSelectedCommandHandler $updatePaymentMethodSelectedCommandHandler,
        private CreateOrderCommandHandler $createOrderCommandHandler,
        private CapturePayPalOrderCommandHandler $capturePayPalOrderCommandHandler,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            CheckoutCompletedEvent::class => [
                ['updatePaymentMethodSelected'],
                ['proceedToPayment'],
            ],
        ];
    }

    /**
     * Update the payment method selected
     * - funding source name
     * - isHostedFields
     * - isExpressCheckout
     *
     * @param CheckoutCompletedEvent $event
     *
     * @return void
     *
     * @throws CartException
     * @throws PayPalOrderException
     */
    public function updatePaymentMethodSelected(CheckoutCompletedEvent $event)
    {
        $this->updatePaymentMethodSelectedCommandHandler->handle(new UpdatePaymentMethodSelectedCommand(
            $event->getCartId()->getValue(),
            $event->getPayPalOrderId()->getValue(),
            $event->getFundingSource(),
            $event->isExpressCheckout(),
            $event->isHostedFields()
        ));
    }

    /**
     * Fetch PayPal Order from API
     *
     * @param CheckoutCompletedEvent $event
     *
     * @return void
     *
     * @throws PayPalException
     * @throws PayPalOrderException
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     * @throws PsCheckoutException
     * @throws HttpTimeoutException
     */
    public function proceedToPayment(CheckoutCompletedEvent $event)
    {
        $payPalOrderId = $event->getPayPalOrderId()->getValue();

        /** @var GetPayPalOrderForCheckoutCompletedQueryResult $getPayPalOrderForCheckoutCompletedQueryResult */
        $getPayPalOrderForCheckoutCompletedQueryResult = $this->queryBus->handle(new GetPayPalOrderForCheckoutCompletedQuery(
            $payPalOrderId
        ));

        $payPalOrder = $getPayPalOrderForCheckoutCompletedQueryResult->getPayPalOrder();

        try {
            $this->checkoutChecker->continueWithAuthorization($event->getCartId()->getValue(), $payPalOrder);
        } catch (PsCheckoutException $exception) {
            if ($exception->getCode() === PsCheckoutException::PAYPAL_ORDER_ALREADY_CAPTURED) {
                $capture = isset($payPalOrder['purchase_units'][0]['payments']['captures'][0]) ? $payPalOrder['purchase_units'][0]['payments']['captures'][0] : null;
                $this->createOrderCommandHandler->handle(new CreateOrderCommand($payPalOrderId, $capture));

                return;
            } else {
                throw $exception;
            }
        }

        try {
            $this->capturePayPalOrderCommandHandler->handle(
                new CapturePayPalOrderCommand(
                    $payPalOrderId,
                    $event->getFundingSource()
                )
            );
        } catch (PayPalException $exception) {
            if ($exception->getCode() === PayPalException::ORDER_NOT_APPROVED) {
                $this->createOrderCommandHandler->handle(new CreateOrderCommand($payPalOrderId));

                return;
            } elseif ($exception->getCode() === PayPalException::RESOURCE_NOT_FOUND) {
                $psCheckoutCart = $this->psCheckoutCartRepository->findOneByPayPalOrderId($payPalOrderId);

                if (\Validate::isLoadedObject($psCheckoutCart)) {
                    $psCheckoutCart->paypal_status = \PsCheckoutCart::STATUS_CANCELED;
                    $this->psCheckoutCartRepository->save($psCheckoutCart);
                }

                throw $exception;
            } elseif ($exception->getCode() === PayPalException::ORDER_ALREADY_CAPTURED) {
                if (isset($payPalOrder['purchase_units'][0]['payments']['captures'][0])) {
                    $capture = $payPalOrder['purchase_units'][0]['payments']['captures'][0];
                } else {
                    $payPalOrderQuery = new GetPayPalOrderForCheckoutCompletedQuery($payPalOrderId);

                    /** @var GetPayPalOrderForCheckoutCompletedQueryResult $getPayPalOrderForCheckoutCompletedQueryResult */
                    $getPayPalOrderForCheckoutCompletedQueryResult = $this->queryBus->handle($payPalOrderQuery);
                    $payPalOrder = $getPayPalOrderForCheckoutCompletedQueryResult->getPayPalOrder();
                    $capture = isset($payPalOrder['purchase_units'][0]['payments']['captures'][0]) ? $payPalOrder['purchase_units'][0]['payments']['captures'][0] : null;
                }
                $this->createOrderCommandHandler->handle(new CreateOrderCommand($payPalOrderId, $capture));

                return;
            } else {
                throw $exception;
            }
        }
    }
}

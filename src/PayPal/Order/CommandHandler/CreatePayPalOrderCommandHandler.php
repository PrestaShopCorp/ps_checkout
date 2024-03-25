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

use PrestaShop\Module\PrestashopCheckout\Api\Payment\PaymentService;
use PrestaShop\Module\PrestashopCheckout\Builder\Payload\OrderPayloadBuilder;
use PrestaShop\Module\PrestashopCheckout\Cart\Exception\CartNotFoundException;
use PrestaShop\Module\PrestashopCheckout\Context\PrestaShopContext;
use PrestaShop\Module\PrestashopCheckout\Customer\ValueObject\CustomerId;
use PrestaShop\Module\PrestashopCheckout\Entity\PayPalOrder;
use PrestaShop\Module\PrestashopCheckout\Event\EventDispatcherInterface;
use PrestaShop\Module\PrestashopCheckout\Exception\InvalidRequestException;
use PrestaShop\Module\PrestashopCheckout\Exception\NotAuthorizedException;
use PrestaShop\Module\PrestashopCheckout\Exception\UnprocessableEntityException;
use PrestaShop\Module\PrestashopCheckout\Http\MaaslandHttpClient;
use PrestaShop\Module\PrestashopCheckout\PayPal\Customer\ValueObject\PayPalCustomerId;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Command\CreatePayPalOrderCommand;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Event\PayPalOrderCreatedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Exception\PayPalOrderException;
use PrestaShop\Module\PrestashopCheckout\Presenter\Cart\CartPresenter;
use PrestaShop\Module\PrestashopCheckout\Repository\PaymentTokenRepository;
use PrestaShop\Module\PrestashopCheckout\Repository\PayPalCustomerRepository;
use PrestaShop\Module\PrestashopCheckout\Serializer\ObjectSerializerInterface;
use PrestaShop\Module\PrestashopCheckout\ShopContext;
use PrestaShop\PrestaShop\Core\Foundation\IoC\Exception;

class CreatePayPalOrderCommandHandler
{
    /**
     * @var MaaslandHttpClient
     */
    private $maaslandHttpClient;
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;
    /**
     * @var PaymentService
     */
    private $paymentService;
    /**
     * @var ObjectSerializerInterface
     */
    private $objectSerializer;
    /**
     * @var PayPalCustomerRepository
     */
    private $payPalCustomerRepository;
    /**
     * @var PaymentTokenRepository
     */
    private $paymentTokenRepository;
    /**
     * @var ShopContext
     */
    private $shopContext;
    /**
     * @var PrestaShopContext
     */
    private $prestaShopContext;

    public function __construct(
        MaaslandHttpClient $maaslandHttpClient,
        ShopContext $shopContext,
        PrestaShopContext $prestaShopContext,
        EventDispatcherInterface $eventDispatcher,
        PaymentService $paymentService,
        ObjectSerializerInterface $objectSerializer,
        PayPalCustomerRepository $payPalCustomerRepository,
        PaymentTokenRepository $paymentTokenRepository
    ) {
        $this->maaslandHttpClient = $maaslandHttpClient;
        $this->shopContext = $shopContext;
        $this->eventDispatcher = $eventDispatcher;
        $this->paymentService = $paymentService;
        $this->objectSerializer = $objectSerializer;
        $this->payPalCustomerRepository = $payPalCustomerRepository;
        $this->paymentTokenRepository = $paymentTokenRepository;
        $this->prestaShopContext = $prestaShopContext;
    }

    /**
     * @param CreatePayPalOrderCommand $command
     *
     * @return void
     *
     * @throws CartNotFoundException
     * @throws PayPalOrderException
     * @throws InvalidRequestException
     * @throws NotAuthorizedException
     * @throws UnprocessableEntityException
     * @throws Exception
     */
    public function handle(CreatePayPalOrderCommand $command)
    {
//        $cart = $this->cartRepository->getCartById($command->getCartId());
//
//        if ($command->getPaymentTokenId()) {
//            $paymentToken = $this->paymentTokenRepository->findById($command->getPaymentTokenId());
//            $payPalCustomerId = $this->payPalCustomerRepository->findPayPalCustomerIdByCustomerId($cart->getCustomer()->id);
//            if (!$paymentToken || !$payPalCustomerId || $paymentToken->getPayPalCustomerId()->getValue() !== $payPalCustomerId->getValue()) {
//                throw new Exception('Payment token does not belong to the customer');
//            }
//        }
//
//        $payload = $this->createPayPalOrderPayloadBuilder->build($cart, $command->getFundingSource(), $command->vault(), $command->getPaymentTokenId());
//        $order = $this->paymentService->createOrder($payload);

        $cartPresenter = (new CartPresenter())->present();
        $builder = new OrderPayloadBuilder($cartPresenter);
        $builder->setIsCard($command->getFundingSource() === 'card');
        $builder->setExpressCheckout($command->isExpressCheckout());

        if ($this->shopContext->isShop17()) {
            // Build full payload in 1.7
            $builder->buildFullPayload();
        } else {
            // if on 1.6 always build minimal payload
            $builder->buildMinimalPayload();
        }

        $response = $this->maaslandHttpClient->createOrder($builder->presentPayload()->getArray());
        $order = json_decode($response->getBody(), true);

        $customerIntent = [];

        if ($command->vault()) {
            $customerIntent[] = PayPalOrder::CUSTOMER_INTENT_VAULT;

            if (isset($order['payment_source']['paypal']['attributes']['vault']['customer']['id'])) {
                try {
                    $payPalCustomerId = new PayPalCustomerId($order['payment_source']['paypal']['attributes']['vault']['customer']['id']);
                    $customerId = new CustomerId($this->prestaShopContext->getCustomerId());
                    $this->payPalCustomerRepository->save($customerId, $payPalCustomerId);
                } catch (\Exception $exception) {
                }
            }
        }

        if ($command->favorite()) {
            $customerIntent[] = PayPalOrder::CUSTOMER_INTENT_FAVORITE;
            if ($command->getPaymentTokenId()) {
                $this->paymentTokenRepository->setTokenFavorite($command->getPaymentTokenId());
            }
        }

        $this->eventDispatcher->dispatch(new PayPalOrderCreatedEvent(
            $order['id'],
            $order,
            $command->getCartId()->getValue(),
            $command->getFundingSource(),
            $command->isHostedFields(),
            $command->isExpressCheckout(),
            !empty($customerIntent) ? implode(',', $customerIntent) : null
        ));
    }
}

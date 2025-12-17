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

namespace PsCheckout\Core\PayPal\Order\Action;

use PsCheckout\Api\Http\OrderHttpClientInterface;
use PsCheckout\Api\ValueObject\PayPalOrderResponse;
use PsCheckout\Core\Exception\PsCheckoutException;
use PsCheckout\Core\PayPal\Order\Cache\PayPalOrderCacheInterface;
use PsCheckout\Core\PayPal\Order\Configuration\PayPalAuthorizationStatus;
use PsCheckout\Core\PayPal\Order\Configuration\PayPalOrderStatus;
use PsCheckout\Core\PayPal\Order\Entity\PayPalOrderAuthorization;
use PsCheckout\Core\PayPal\Order\Handler\EventHandlerInterface;
use PsCheckout\Core\PayPal\Order\Provider\PayPalOrderProviderInterface;
use PsCheckout\Core\PayPal\Order\Repository\PayPalOrderAuthorizationRepositoryInterface;

class AuthorizePayPalOrderAction implements AuthorizePayPalOrderActionInterface
{
    /**
     * @var OrderHttpClientInterface
     */
    private $orderHttpClient;

    /**
     * @var PayPalOrderCacheInterface
     */
    private $payPalOrderCache;

    /**
     * @var EventHandlerInterface
     */
    private $paymentPendingEventHandler;

    /**
     * @var EventHandlerInterface
     */
    private $paymentDeniedEventHandler;

    /**
     * @var PayPalOrderProviderInterface
     */
    private $payPalOrderProvider;

    /**
     * @var PayPalOrderAuthorizationRepositoryInterface
     */
    private $payPalOrderAuthorizationRepository;

    public function __construct(
        OrderHttpClientInterface $orderHttpClient,
        PayPalOrderCacheInterface $payPalOrderCache,
        EventHandlerInterface $paymentPendingEventHandler,
        EventHandlerInterface $paymentDeniedEventHandler,
        PayPalOrderProviderInterface $payPalOrderProvider,
        PayPalOrderAuthorizationRepositoryInterface $payPalOrderAuthorizationRepository
    ) {
        $this->orderHttpClient = $orderHttpClient;
        $this->payPalOrderCache = $payPalOrderCache;
        $this->paymentPendingEventHandler = $paymentPendingEventHandler;
        $this->paymentDeniedEventHandler = $paymentDeniedEventHandler;
        $this->payPalOrderProvider = $payPalOrderProvider;
        $this->payPalOrderAuthorizationRepository = $payPalOrderAuthorizationRepository;
    }

    /**
     * {@inheritDoc}
     */
    public function execute(PayPalOrderResponse $payPalOrder): PayPalOrderResponse
    {
        if ($payPalOrder->getStatus() !== PayPalOrderStatus::APPROVED) {
            throw new PsCheckoutException(sprintf('PayPal Order %s status must be APPROVED, current status: %s', $payPalOrder->getId(), $payPalOrder->getStatus()), PsCheckoutException::PAYPAL_ORDER_STATUS_INVALID);
        }

        $response = $this->orderHttpClient->authorizeOrder($payPalOrder->getId(), []);

        $orderPayPal = json_decode($response->getBody(), true);
        $cachedOrder = $this->payPalOrderCache->getValue($orderPayPal['id']);

        $this->payPalOrderCache->set($orderPayPal['id'], array_replace_recursive($cachedOrder, $orderPayPal));

        $payPalOrderResponse = $this->payPalOrderProvider->getById($orderPayPal['id']);

        $authorization = $payPalOrderResponse->getAuthorization();

        $payPalAuthorization = new PayPalOrderAuthorization(
            $authorization['id'],
            $orderPayPal['id'],
            $authorization['status'],
            $authorization['expiration_time'],
            $authorization['create_time'],
            $authorization['update_time']
        );

        $this->payPalOrderAuthorizationRepository->save($payPalAuthorization);

        $authorizationStatus = $authorization['status'];

        if (in_array($authorizationStatus, [PayPalAuthorizationStatus::CREATED, PayPalAuthorizationStatus::PENDING])) {
            $this->paymentPendingEventHandler->handle($payPalOrderResponse);
        }

        if ($authorizationStatus === PayPalAuthorizationStatus::DENIED) {
            $this->paymentDeniedEventHandler->handle($payPalOrderResponse);
        }

        return $payPalOrderResponse;
    }
}

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

use PsCheckout\Api\Http\PaymentHttpClientInterface;
use PsCheckout\Api\ValueObject\PayPalOrderResponse;
use PsCheckout\Core\Exception\PsCheckoutException;
use PsCheckout\Core\PayPal\Order\Configuration\PayPalAuthorizationStatus;
use PsCheckout\Core\PayPal\Order\Configuration\PayPalOrderStatus;
use PsCheckout\Core\PayPal\Order\Entity\PayPalOrderAuthorization;
use PsCheckout\Core\PayPal\Order\Repository\PayPalOrderAuthorizationRepositoryInterface;
use Psr\Log\LoggerInterface;

class VoidAuthorizationAction implements VoidAuthorizationActionInterface
{
    /**
     * @var PaymentHttpClientInterface
     */
    private $paymentHttpClient;

    /**
     * @var PayPalOrderAuthorizationRepositoryInterface
     */
    private $authorizationRepository;

    public function __construct(
        PaymentHttpClientInterface $paymentHttpClient,
        PayPalOrderAuthorizationRepositoryInterface $authorizationRepository
    ) {
        $this->paymentHttpClient = $paymentHttpClient;
        $this->authorizationRepository = $authorizationRepository;
    }

    /**
     * {@inheritDoc}
     */
    public function execute(PayPalOrderResponse $payPalOrder): PayPalOrderAuthorization
    {
        // Check intent must be AUTHORIZE
        if ($payPalOrder->getIntent() !== 'AUTHORIZE') {
            throw new PsCheckoutException(
                sprintf('PayPal Order %s intent must be AUTHORIZE, current intent: %s', $payPalOrder->getId(), $payPalOrder->getIntent()),
                PsCheckoutException::PAYPAL_ORDER_INTENT_INVALID
            );
        }

        // Fetch payment authorization from order
        $authorization = $payPalOrder->getAuthorization();

        if (!$authorization || !isset($authorization['id'])) {
            throw new PsCheckoutException(
                sprintf('PayPal Order %s does not have a valid authorization', $payPalOrder->getId()),
                PsCheckoutException::PAYPAL_AUTHORIZATION_NOT_FOUND
            );
        }

        $authorizationId = $authorization['id'];
        $authorizationStatus = $authorization['status'];

        // Check PayPal order status must be APPROVED
        if (!in_array(
            $authorizationStatus,
            [
                PayPalAuthorizationStatus::PENDING,
                PayPalAuthorizationStatus::CREATED,
                PayPalAuthorizationStatus::PARTIALLY_CAPTURED
            ]
        )) {
            throw new PsCheckoutException(
                sprintf('PayPal Order Authorization %s status must be PENDING, CREATED or PARTIALLY_CAPTURED , current status: %s', $authorizationId, $authorizationStatus),
                PsCheckoutException::PAYPAL_AUTHORIZATION_STATUS_INVALID
            );
        }

        // Call captureAuthorization in PaymentHttpClient
        $voidResponse = $this->paymentHttpClient->voidAuthorization($authorizationId);
        $voidedAuthorization = json_decode($voidResponse->getBody(), true);

        $authorizationEntity = $this->authorizationRepository->getById($authorizationId);

        $authorizationEntity->setStatus($voidedAuthorization['status'])
        ->setUpdateTime($voidedAuthorization['update_time']);

        $this->authorizationRepository->save($authorizationEntity);

        return $authorizationEntity;
    }
}

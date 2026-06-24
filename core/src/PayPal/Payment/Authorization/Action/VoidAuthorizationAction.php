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

namespace PsCheckout\Core\PayPal\Payment\Authorization\Action;

use PsCheckout\Api\Http\PaymentHttpClientInterface;
use PsCheckout\Api\ValueObject\PayPalOrderResponse;
use PsCheckout\Core\Exception\PsCheckoutException;
use PsCheckout\Core\PayPal\Order\Configuration\PayPalAuthorizationStatus;
use PsCheckout\Core\PayPal\Order\Configuration\PayPalOrderIntent;
use PsCheckout\Core\PayPal\Order\Entity\PayPalOrderAuthorization;
use PsCheckout\Core\PayPal\Order\Repository\PayPalOrderAuthorizationRepositoryInterface;
use PsCheckout\Core\PayPal\Payment\Authorization\Configuration\AuthorizationAction;
use Psr\Log\LoggerInterface;

final class VoidAuthorizationAction implements AuthorizationActionInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var PaymentHttpClientInterface
     */
    private $paymentHttpClient;

    /**
     * @var PayPalOrderAuthorizationRepositoryInterface
     */
    private $authorizationRepository;

    public function __construct(
        LoggerInterface $logger,
        PaymentHttpClientInterface $paymentHttpClient,
        PayPalOrderAuthorizationRepositoryInterface $authorizationRepository
    ) {
        $this->logger = $logger;
        $this->paymentHttpClient = $paymentHttpClient;
        $this->authorizationRepository = $authorizationRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(string $action): bool
    {
        return $action === AuthorizationAction::VOID;
    }

    /**
     * {@inheritDoc}
     */
    public function execute(PayPalOrderResponse $payPalOrder, array $payload = []): PayPalOrderAuthorization
    {
        // Check intent must be AUTHORIZE
        if ($payPalOrder->getIntent() !== PayPalOrderIntent::AUTHORIZE) {
            throw new PsCheckoutException(
                sprintf('PayPal Order %s intent must be AUTHORIZE, current intent: %s', $payPalOrder->getId(), $payPalOrder->getIntent()),
                PsCheckoutException::PAYPAL_ORDER_INTENT_INVALID
            );
        }

        // Fetch payment authorization from order
        $authorization = $payPalOrder->getAuthorization();

        if (!$authorization) {
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

        $this->logger->info('Voiding authorization', [
            'order_id' => $payPalOrder->getId(),
            'authorization_id' => $authorizationId,
            'authorization_status' => $authorizationStatus,
        ]);

        // Call voidAuthorization in PaymentHttpClient
        $voidResponse = $this->paymentHttpClient->voidAuthorization($authorizationId);

        /**
         * @var array{
         *      id: string,
         *      status: string,
         *      expiration_time: string,
         *      create_time: string,
         *      update_time: string
         *  }|null $voidedAuthorization
         */
        $voidedAuthorization = json_decode($voidResponse->getBody(), true);

        if (empty($voidedAuthorization)) {
            $this->logger->error('Invalid void response for authorization', [
                'order_id' => $payPalOrder->getId(),
                'authorization_id' => $authorizationId,
            ]);

            throw new PsCheckoutException(
                sprintf('Invalid void response for authorization %s', $authorizationId),
                PsCheckoutException::PAYPAL_AUTHORIZATION_NOT_FOUND
            );
        }

        $authorizationEntity = $this->authorizationRepository->getById($authorizationId);

        if (!$authorizationEntity) {
            throw new PsCheckoutException(
                sprintf('Authorization entity %s not found in repository', $authorizationId),
                PsCheckoutException::PAYPAL_AUTHORIZATION_NOT_FOUND
            );
        }

        $authorizationEntity->setStatus($voidedAuthorization['status'])
        ->setUpdateTime($voidedAuthorization['update_time']);

        $this->authorizationRepository->save($authorizationEntity);

        return $authorizationEntity;
    }
}

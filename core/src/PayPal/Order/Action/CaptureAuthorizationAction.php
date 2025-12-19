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

class CaptureAuthorizationAction implements CaptureAuthorizationActionInterface
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
        // Check PayPal order status must be APPROVED
        if ($payPalOrder->getStatus() !== PayPalOrderStatus::APPROVED) {
            throw new PsCheckoutException(
                sprintf('PayPal Order %s status must be APPROVED, current status: %s', $payPalOrder->getId(), $payPalOrder->getStatus()),
                PsCheckoutException::PAYPAL_ORDER_STATUS_INVALID
            );
        }

        // Check intent must be AUTHORIZE
        if ($payPalOrder->getIntent() !== 'AUTHORIZE') {
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

        // Check if status is VOIDED
        if ($authorizationStatus === PayPalAuthorizationStatus::VOIDED) {
            throw new PsCheckoutException(
                "Authorization $authorizationId is voided and cannot be captured",
                PsCheckoutException::PAYPAL_AUTHORIZATION_VOIDED
            );
        }

        // Validate authorization status must be CREATED or PARTIALLY_CAPTURED
        if (!in_array($authorizationStatus, [PayPalAuthorizationStatus::CREATED, PayPalAuthorizationStatus::PARTIALLY_CAPTURED], true)) {
            throw new PsCheckoutException(
                "Authorization $authorizationId status must be CREATED or PARTIALLY_CAPTURED, current status: $authorizationStatus",
                PsCheckoutException::PAYPAL_AUTHORIZATION_STATUS_INVALID
            );
        }

        if (isset($authorization['expiration_time'])) {
            $expirationTime = new \DateTime((string) $authorization['expiration_time']);
        } else {
            $expirationTime = new \DateTime((string) $authorization['create_time']);
            $expirationTime->modify('+30 days');
        }

        $currentTime = new \DateTime('now', new \DateTimeZone('UTC'));

        if ($expirationTime < $currentTime) {
            throw new PsCheckoutException(
                "Authorization $authorizationId has expired",
                PsCheckoutException::PAYPAL_AUTHORIZATION_EXPIRED
            );
        }

        $captureResponse = $this->paymentHttpClient->captureAuthorization($authorizationId);

        /**
         * @var array{
         *     id: string,
         *     status: string,
         *     create_time: string,
         *     update_time: string
         * } $capturedAuthorization
         */
        $capturedAuthorization = json_decode($captureResponse->getBody(), true);

        $authorizationEntity = $this->authorizationRepository->getById($authorizationId);

        if ($authorizationEntity) {
            $authorizationEntity->setStatus($capturedAuthorization['status']);
        } else {
            $authorizationEntity = new PayPalOrderAuthorization(
                $authorizationId,
                $payPalOrder->getId(),
                $capturedAuthorization['status'],
                '',
                $capturedAuthorization['create_time'],
                $capturedAuthorization['update_time'],
            );
        }

        $this->authorizationRepository->save($authorizationEntity);

        return $authorizationEntity;
    }
}

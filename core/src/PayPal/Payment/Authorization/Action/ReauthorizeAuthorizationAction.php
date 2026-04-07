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

use PsCheckout\Api\Dto\PayPal\LinkDescription;
use PsCheckout\Api\Dto\PayPal\Payment\AuthorizationLinkRelation;
use PsCheckout\Api\Dto\PayPal\Payment\AuthorizationStatus;
use PsCheckout\Api\Http\PaymentHttpClientInterface;
use PsCheckout\Api\ValueObject\PayPalOrderResponse;
use PsCheckout\Core\Exception\PsCheckoutException;
use PsCheckout\Core\OrderState\Action\SetOrderStateActionInterface;
use PsCheckout\Core\PayPal\Order\Configuration\PayPalAuthorizationStatus;
use PsCheckout\Core\PayPal\Order\Configuration\PayPalOrderIntent;
use PsCheckout\Core\PayPal\Order\Entity\PayPalOrderAuthorization;
use PsCheckout\Core\PayPal\Order\Handler\EventHandlerInterface;
use PsCheckout\Core\PayPal\Order\Repository\PayPalOrderAuthorizationRepositoryInterface;
use PsCheckout\Core\PayPal\Payment\Authorization\Configuration\AuthorizationAction;
use Psr\Log\LoggerInterface;

final class ReauthorizeAuthorizationAction implements AuthorizationActionInterface
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
    private $payPalOrderAuthorizationRepository;

    /**
     * @var SetOrderStateActionInterface
     */
    private $setPendingOrderStateAction;

    /**
     * @var EventHandlerInterface
     */
    private $paymentDeniedEventHandler;

    public function __construct(
        LoggerInterface $logger,
        PaymentHttpClientInterface $paymentHttpClient,
        PayPalOrderAuthorizationRepositoryInterface $payPalOrderAuthorizationRepository,
        SetOrderStateActionInterface $setPendingOrderStateAction,
        EventHandlerInterface $paymentDeniedEventHandler
    ) {
        $this->logger = $logger;
        $this->paymentHttpClient = $paymentHttpClient;
        $this->payPalOrderAuthorizationRepository = $payPalOrderAuthorizationRepository;
        $this->setPendingOrderStateAction = $setPendingOrderStateAction;
        $this->paymentDeniedEventHandler = $paymentDeniedEventHandler;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(string $action): bool
    {
        return $action === AuthorizationAction::REAUTHORIZE;
    }

    /**
     * @inheritDoc
     */
    public function execute(PayPalOrderResponse $payPalOrder, array $payload = [])
    {
        if ($payPalOrder->getIntent() !== PayPalOrderIntent::AUTHORIZE) {
            $this->logger->error('PayPal Order intent must be AUTHORIZE', ['order_id' => $payPalOrder->getId()]);

            throw new PsCheckoutException(sprintf('PayPal Order %s intent must be AUTHORIZE, current intent: %s', $payPalOrder->getId(), $payPalOrder->getIntent()), PsCheckoutException::PAYPAL_ORDER_INTENT_INVALID);
        }

        try {
            $authorizations = array_values(array_filter($payPalOrder->getAuthorizations(), static function (array $authorization) {
                return in_array($authorization['status'], [PayPalAuthorizationStatus::CREATED, PayPalAuthorizationStatus::PARTIALLY_CAPTURED], true);
            }));
        } catch (\Throwable $exception) {
            $this->logger->error('PayPal Order authorizations retrieval failed', ['exception' => $exception, 'order_id' => $payPalOrder->getId()]);

            throw new PsCheckoutException(sprintf('PayPal Order %s contains invalid authorizations', $payPalOrder->getStatus()), PsCheckoutException::PAYPAL_ORDER_AUTHORIZATIONS_INVALID, $exception);
        }

        if (empty($authorizations)) {
            $this->logger->error('PayPal Order does not contain reauthorize-able authorizations', ['order_id' => $payPalOrder->getId()]);

            throw new PsCheckoutException(sprintf('PayPal Order %s does not contain reauthorize-able authorizations', $payPalOrder->getId()), PsCheckoutException::PAYPAL_ORDER_AUTHORIZATIONS_EMPTY);
        }

        if (1 < count($authorizations)) {
            $this->logger->error('PayPal Order contains more than one reauthorize-able authorizations', [
                'order_id' => $payPalOrder->getId(),
                'authorizations' => array_column($authorizations, 'id')
            ]);

            throw new PsCheckoutException(sprintf('PayPal Order %s contains more than one reauthorize-able authorizations', $payPalOrder->getId()), PsCheckoutException::PAYPAL_ORDER_AUTHORIZATIONS_NOT_UNIQUE);
        }

        $parentAuthorization = $this->paymentHttpClient->getAuthorization($authorizations[0]['id']);
        $reauthorizeLinkRel = !empty($parentAuthorization->getLinks()) ? array_filter($parentAuthorization->getLinks(), static function (LinkDescription $link) {
            return $link->getRel() === AuthorizationLinkRelation::REAUTHORIZE;
        }) : false;
        if ($reauthorizeLinkRel === false || !in_array($parentAuthorization->getStatus(), [AuthorizationStatus::CREATED, AuthorizationStatus::PARTIALLY_CAPTURED], true)) {
            $this->logger->error('PayPal Order authorization is not reauthorize-able', [
                'order_id' => $payPalOrder->getId(),
                'authorization_id' => $parentAuthorization->getId(),
            ]);

            throw new PsCheckoutException(sprintf('PayPal Authorization %s is not reauthorize-able, current status: %s', $parentAuthorization->getId(), $parentAuthorization->getStatus()), PsCheckoutException::PAYPAL_AUTHORIZATION_STATUS_INVALID);
        }

        try {
            $reauthorization = $this->paymentHttpClient->reauthorizeAuthorization($parentAuthorization->getId());
        } catch (\Throwable $exception) {
            $this->logger->error('PayPal Order reauthorization failed', [
                'exception' => $exception,
                'order_id' => $payPalOrder->getId(),
                'authorization_id' => $parentAuthorization->getId(),
            ]);

            throw new PsCheckoutException(
                sprintf('PayPal Order %s re-authorization failure for authorization %s', $payPalOrder->getId(), $parentAuthorization->getId()),
                PsCheckoutException::PAYPAL_AUTHORIZATION_REAUTHORIZATION_FAILURE,
                $exception
            );
        }

        try {
            $payPalReauthorization = new PayPalOrderAuthorization(
                $reauthorization->getId(),
                $payPalOrder->getId(),
                $reauthorization->getStatus(),
                $reauthorization->getExpirationTime(),
                $reauthorization->getCreateTime(),
                $reauthorization->getUpdateTime()
            );

            $this->payPalOrderAuthorizationRepository->save($payPalReauthorization);
        } catch (\Throwable $exception) {
            $this->logger->error('PayPal Order reauthorization save failed', [
                'exception' => $exception,
                'order_id' => $payPalOrder->getId(),
                'authorization_id' => $parentAuthorization->getId(),
                'reauthorization_id' => $reauthorization->getId(),
            ]);

            throw new PsCheckoutException(
                sprintf('PayPal Order %s re-authorization database failure for authorization %s', $payPalOrder->getId(), $reauthorization->getId()),
                PsCheckoutException::PAYPAL_AUTHORIZATION_DATABASE_FAILURE,
                $exception
            );
        }

        if (in_array($reauthorization->getStatus(), [PayPalAuthorizationStatus::CREATED, PayPalAuthorizationStatus::PENDING], true)) {
            $this->logger->info(sprintf('PayPal Order %s re-authorization %s is %s', $payPalOrder->getId(), $reauthorization->getId(), $reauthorization->getStatus()));
            $this->setPendingOrderStateAction->execute($payPalOrder->getId());
        }

        if ($reauthorization->getStatus() === PayPalAuthorizationStatus::DENIED) {
            $this->logger->info(sprintf('PayPal Order %s re-authorization %s is denied', $payPalOrder->getId(), $reauthorization->getId()));
            $this->paymentDeniedEventHandler->handle($payPalOrder);
        }

        return $payPalReauthorization;
    }
}

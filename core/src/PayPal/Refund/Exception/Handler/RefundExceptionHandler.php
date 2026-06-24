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

namespace PsCheckout\Core\PayPal\Refund\Exception\Handler;

use PsCheckout\Api\Http\Exception\PayPalException;
use PsCheckout\Core\Order\Exception\OrderException;
use PsCheckout\Core\PayPal\Refund\Exception\PayPalRefundException;
use PsCheckout\Presentation\TranslatorInterface;
use Psr\Log\LoggerInterface;

class RefundExceptionHandler implements RefundExceptionHandlerInterface
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        TranslatorInterface $translator,
        LoggerInterface $logger
    ) {
        $this->translator = $translator;
        $this->logger = $logger;
    }

    /**
     * {@inheritDoc}
     */
    public function handle(\Exception $exception): array
    {
        if ($exception instanceof PayPalRefundException) {
            return $this->handlePayPalRefundException($exception);
        }

        if ($exception instanceof PayPalException) {
            return $this->handlePayPalException($exception);
        }

        if ($exception instanceof OrderException) {
            return $this->handleOrderException($exception);
        }

        return $this->handleGenericException($exception);
    }

    /**
     * @param PayPalRefundException $exception
     *
     * @return array<string, mixed>
     */
    private function handlePayPalRefundException(PayPalRefundException $exception): array
    {
        switch ($exception->getCode()) {
            case PayPalRefundException::INVALID_ORDER_ID:
                $error = $this->translator->trans('PayPal Order is invalid.');

                break;
            case PayPalRefundException::INVALID_TRANSACTION_ID:
                $error = $this->translator->trans('PayPal Transaction is invalid.');

                break;
            case PayPalRefundException::INVALID_CURRENCY:
                $error = $this->translator->trans('PayPal refund currency is invalid.');

                break;
            case PayPalRefundException::INVALID_AMOUNT:
                $error = $this->translator->trans('PayPal refund amount is invalid.');

                break;
            case PayPalRefundException::REFUND_FAILED:
                $error = $this->translator->trans('PayPal refund failed.');

                break;
            default:
                $error = $this->translator->trans('An unexpected refund error occurred.') . ' (' . $exception->getMessage() . ')';

                break;
        }

        return [
            'httpCode' => 400,
            'status' => false,
            'errors' => [$error],
        ];
    }

    /**
     * @param PayPalException $exception
     *
     * @return array<string, mixed>
     */
    private function handlePayPalException(PayPalException $exception): array
    {
        $this->logger->error('ajaxProcessRefundOrder - PayPalException ' . $exception->getCode(), [
            'exception' => $exception,
        ]);

        switch ($exception->getCode()) {
            case PayPalException::REFUND_TIME_LIMIT_EXCEEDED:
                $error = $this->translator->trans('The refund time limit has been exceeded for this transaction.');

                break;
            case PayPalException::REFUND_FAILED_INSUFFICIENT_FUNDS:
                $error = $this->translator->trans('Refund failed due to insufficient funds in the PayPal account.');

                break;
            case PayPalException::REFUND_NOT_ALLOWED:
                $error = $this->translator->trans('A full refund is not allowed because a partial refund has already been issued.');

                break;
            case PayPalException::REFUND_CAPTURE_CURRENCY_MISMATCH:
                $error = $this->translator->trans('The refund currency must match the capture currency.');

                break;
            case PayPalException::REFUND_AMOUNT_EXCEEDED:
                $error = $this->translator->trans('The refund amount exceeds the remaining capturable amount.');

                break;
            case PayPalException::CAPTURE_FULLY_REFUNDED:
                $error = $this->translator->trans('This capture has already been fully refunded.');

                break;
            case PayPalException::CAPTURE_DISPUTED_PARTIAL_REFUND_NOT_ALLOWED:
                $error = $this->translator->trans('A partial refund cannot be issued while there is an open dispute on this capture.');

                break;
            case PayPalException::REFUND_NOT_PERMITTED_DUE_TO_CHARGEBACK:
                $error = $this->translator->trans('Refund is not permitted due to a chargeback on this transaction.');

                break;
            case PayPalException::MAX_NUMBER_OF_REFUNDS_EXCEEDED:
                $error = $this->translator->trans('The maximum number of refunds for this capture has been reached.');

                break;
            case PayPalException::PARTIAL_REFUND_NOT_ALLOWED:
                $error = $this->translator->trans('Partial refund is not allowed for this capture. Only a full refund can be issued.');

                break;
            case PayPalException::PENDING_CAPTURE:
                $error = $this->translator->trans('Cannot refund a pending capture. Please wait until the capture is completed.');

                break;
            case PayPalException::CANNOT_PROCESS_REFUNDS:
                $error = $this->translator->trans('PayPal cannot process refunds at this time. Please try again later.');

                break;
            case PayPalException::INVALID_REFUND_AMOUNT:
                $error = $this->translator->trans('The refund amount is invalid.');

                break;
            case PayPalException::REFUND_AMOUNT_TOO_LOW:
                $error = $this->translator->trans('The refund amount is too low.');

                break;
            case PayPalException::TRANSACTION_DISPUTED:
                $error = $this->translator->trans('This transaction is under dispute. Refund cannot be processed.');

                break;
            case PayPalException::REFUND_IS_RESTRICTED:
                $error = $this->translator->trans('Refund is restricted for this transaction.');

                break;
            case PayPalException::CURRENCY_MISMATCH:
                $error = $this->translator->trans('The currency does not match the capture currency.');

                break;
            default:
                $error = $this->translator->trans('Refund cannot be processed by PayPal.') . ' (' . $exception->getMessage() . ')';

                break;
        }

        return [
            'httpCode' => 400,
            'status' => false,
            'errors' => [$error],
        ];
    }

    /**
     * @param OrderException $exception
     *
     * @return array<string, mixed>
     */
    private function handleOrderException(OrderException $exception): array
    {
        if ($exception->getCode() === OrderException::FAILED_UPDATE_ORDER_STATUS) {
            return [
                'httpCode' => 200,
                'status' => true,
                'content' => $this->translator->trans('Refund has been processed by PayPal, but order status change or email sending failed.'),
            ];
        }

        if ($exception->getCode() !== OrderException::ORDER_HAS_ALREADY_THIS_STATUS) {
            return [
                'httpCode' => 500,
                'status' => false,
                'errors' => [
                    $exception->getMessage(),
                ],
                'error' => $exception->getMessage(),
            ];
        }

        return [
            'httpCode' => 200,
            'status' => true,
            'content' => $this->translator->trans('Refund has been processed by PayPal.'),
        ];
    }

    /**
     * @param \Exception $exception
     *
     * @return array<string, mixed>
     */
    private function handleGenericException(\Exception $exception): array
    {
        $this->logger->error('RefundExceptionHandler - Exception ' . $exception->getCode(), [
            'exception' => $exception,
        ]);

        return [
            'httpCode' => 500,
            'status' => false,
            'errors' => [
                $this->translator->trans('Refund cannot be processed by PayPal.'),
            ],
            'error' => $exception->getMessage(),
        ];
    }
}

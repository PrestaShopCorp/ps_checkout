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

namespace PsCheckout\Presentation\Presenter\OrderSummary;

use Context;
use Currency;
use Order;
use PsCheckout\Api\ValueObject\PayPalOrderResponse;
use PsCheckout\Core\Exception\PsCheckoutException;
use PsCheckout\Core\PayPal\Order\Entity\PayPalOrder;
use PsCheckout\Core\PayPal\Order\Provider\PayPalOrderProviderInterface;
use PsCheckout\Core\PayPal\Order\Provider\PayPalOrderTranslationProviderInterface;
use PsCheckout\Core\PayPal\Order\Repository\PayPalOrderRepositoryInterface;
use PsCheckout\Infrastructure\Adapter\LinkInterface;
use PsCheckout\Presentation\Presenter\FundingSource\FundingSourceTranslationProviderInterface;
use PsCheckout\Presentation\TranslatorInterface;
use Tools;

class OrderSummaryPresenter implements OrderSummaryPresenterInterface
{
    /**
     * @var LinkInterface
     */
    private $link;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var PayPalOrderRepositoryInterface
     */
    private $payPalOrderRepository;

    /**
     * @var PayPalOrderProviderInterface
     */
    private $payPalOrderProvider;

    /**
     * @var FundingSourceTranslationProviderInterface
     */
    private $fundingSourceTranslationProvider;

    /**
     * @var PayPalOrderTranslationProviderInterface
     */
    private $payPalOrderTranslationProvider;

    /**
     * @param LinkInterface $link,
     * @param TranslatorInterface $translator
     * @param PayPalOrderRepositoryInterface $payPalOrderRepository
     * @param PayPalOrderProviderInterface $payPalOrderProvider
     * @param FundingSourceTranslationProviderInterface $fundingSourceTranslationProvider
     * @param PayPalOrderTranslationProviderInterface $payPalOrderTranslationProvider
     */
    public function __construct(
        LinkInterface $link,
        TranslatorInterface $translator,
        PayPalOrderRepositoryInterface $payPalOrderRepository,
        PayPalOrderProviderInterface $payPalOrderProvider,
        FundingSourceTranslationProviderInterface $fundingSourceTranslationProvider,
        PayPalOrderTranslationProviderInterface $payPalOrderTranslationProvider
    ) {
        $this->link = $link;
        $this->translator = $translator;
        $this->payPalOrderRepository = $payPalOrderRepository;
        $this->payPalOrderProvider = $payPalOrderProvider;
        $this->fundingSourceTranslationProvider = $fundingSourceTranslationProvider;
        $this->payPalOrderTranslationProvider = $payPalOrderTranslationProvider;
    }

    /** {@inheritdoc} */
    public function present(Order $order): array
    {
        $payPalOrder = $this->payPalOrderRepository->getOneByCartId($order->id_cart);

        if (!$payPalOrder) {
            throw new PsCheckoutException('PayPal order not found', PsCheckoutException::PAYPAL_ORDER_NOT_FOUND);
        }

        try {
            $payPalOrderResponse = $this->payPalOrderProvider->getById($payPalOrder->getId());
        } catch (PsCheckoutException $exception) {
            $payPalOrderResponse = null;
        }

        $orderTransactionStatus = $payPalOrderResponse ? $payPalOrderResponse->getTransactionStatus() : '';

        return [
            'orderPayPalId' => $payPalOrder->getId(),
            'orderPayPalStatus' => $payPalOrderResponse ? $payPalOrderResponse->getStatus() : $payPalOrder->getStatus(),
            'orderPayPalFundingSourceTranslated' => $this->fundingSourceTranslationProvider->getFundingSourceName($payPalOrder->getFundingSource()),
            'orderPayPalTransactionId' => $payPalOrderResponse ? $payPalOrderResponse->getTransactionId() : '',
            'orderPayPalTransactionStatus' => $orderTransactionStatus,
            'orderPayPalTransactionStatusTranslated' => $this->payPalOrderTranslationProvider->getTransactionStatusTranslated($orderTransactionStatus),
            'orderPayPalTransactionAmount' => $this->getTotalAmountFormatted($payPalOrderResponse),
            'vault' => $payPalOrder->checkCustomerIntent(PayPalOrder::CUSTOMER_INTENT_VAULT),
            'tokenIdentifier' => $this->getPaymentTokenIdentifier($payPalOrder),
            'isTokenSaved' => $this->isTokenSaved($payPalOrder),
            'approvalLink' => $payPalOrderResponse ? $payPalOrderResponse->getApprovalLink() : '',
            'payerActionLink' => $payPalOrderResponse ? $payPalOrderResponse->getPayerActionLink() : '',
            'contactUsLink' => $this->link->getPageLink('contact', ['id_order' => $order->id]),
            'translations' => [
                'blockTitle' => $this->translator->trans('Payment gateway information'),
                'notificationFailed' => $this->translator->trans('Your payment has been declined by our payment gateway, please contact us via the link below.'),
                'notificationPendingApproval' => $this->translator->trans('Your payment needs to be approved, please click the button below.'),
                'notificationPayerActionRequired' => $this->translator->trans('Your payment needs to be authenticated, please click the button below.'),
                'fundingSource' => $this->translator->trans('Funding source'),
                'transactionIdentifier' => $this->translator->trans('Transaction identifier'),
                'transactionStatus' => $this->translator->trans('Transaction status'),
                'amountPaid' => $this->translator->trans('Amount paid'),
                'orderIdentifier' => $this->translator->trans('Order identifier'),
                'orderStatus' => $this->translator->trans('Order status'),
                'externalRedirection' => $this->translator->trans('You will be redirected to an external secured page of our payment gateway.'),
                'paymentMethodStatus' => $this->translator->trans('Payment method status'),
                'paymentTokenSaved' => $this->translator->trans('was saved for future purchases'),
                'paymentTokenNotSaved' => $this->translator->trans('was not saved for future purchases'),
                'contactLink' => $this->translator->trans('If you have any question, please contact us.'),
                'buttonApprove' => $this->translator->trans('Approve payment'),
                'buttonPayerAction' => $this->translator->trans('Authenticate payment'),
            ],
        ];
    }

    /**
     * @param PayPalOrderResponse|null $payPalOrderResponse
     *
     * @return string
     */
    private function getTotalAmountFormatted($payPalOrderResponse): string
    {
        if (!$payPalOrderResponse || !$payPalOrderResponse->getTotalAmount() || !$payPalOrderResponse->getCurrencyCode()) {
            return '';
        }

        if (is_callable(['Tools', 'getContextLocale'])) {
            $locale = Tools::getContextLocale(Context::getContext());

            return $locale->formatPrice((float) $payPalOrderResponse->getTotalAmount(), $payPalOrderResponse->getCurrencyCode());
        } else {
            return Tools::displayPrice(
                (float) $payPalOrderResponse->getTotalAmount(),
                Currency::getCurrencyInstance(Currency::getIdByIsoCode($payPalOrderResponse->getCurrencyCode()))
            );
        }
    }

    /**
     * @param PayPalOrder $payPalOrder
     *
     * @return string
     */
    private function getPaymentTokenIdentifier(PayPalOrder $payPalOrder): string
    {
        $fundingSource = $payPalOrder->getFundingSource();
        $paymentSource = $payPalOrder->getPaymentSource()[$fundingSource] ?? null;

        if (!$paymentSource) {
            return '';
        }

        if ($fundingSource === 'card') {
            return ($paymentSource['brand'] ?? '') . (isset($paymentSource['last_digits']) ? ' *' . $paymentSource['last_digits'] : '');
        } else {
            return $paymentSource['email_address'] ?? '';
        }
    }

    /**
     * @param PayPalOrder $payPalOrder
     *
     * @return bool
     */
    private function isTokenSaved(PayPalOrder $payPalOrder): bool
    {
        $fundingSource = $payPalOrder->getFundingSource();
        $paymentSource = $payPalOrder->getPaymentSource()[$fundingSource] ?? null;

        if (!$paymentSource) {
            return false;
        }

        return isset($paymentSource['attributes']['vault']['id']) &&
            isset($paymentSource['attributes']['vault']['status']) &&
            $paymentSource['attributes']['vault']['status'] === 'VAULTED';
    }
}

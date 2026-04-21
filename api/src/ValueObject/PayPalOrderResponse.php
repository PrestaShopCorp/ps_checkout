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

namespace PsCheckout\Api\ValueObject;

class PayPalOrderResponse
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $status;

    /**
     * @var string
     */
    private $intent;

    /**
     * @var string|null
     */
    private $payer;

    /**
     * @var array|null
     */
    private $paymentSource;

    /**
     * @var array<int, array{
     *     reference_id?: string,
     *     invoice_id?: string,
     *     custom_id?: string,
     *     description?: string,
     *     items?: array<int, array<string, mixed>>,
     *     shipping?: array<string, mixed>,
     *     amount?: array{
     *         value: string,
     *         currency_code: string,
     *     },
     *     payments?: array{
     *         authorizations?: array<int, array{
     *             id: string,
     *             invoice_id?: string,
     *             custom_id?: string,
     *             status: string,
     *             status_details?: array{
     *                 reason: string,
     *             },
     *             amount?: array{
     *                 value: string,
     *                 currency_code: string,
     *             },
     *             seller_protection?: array{
     *                 status: string,
     *                 dispute_categories: array<int, string>,
     *             },
     *             network_transaction_reference?: array{
     *                 id: string,
     *                 date: string,
     *                 network: string,
     *                 acquirer_reference_number: string,
     *             },
     *             create_time?: string,
     *             update_time?: string,
     *             expiration_time?: string,
     *             links?: array<int, array{
     *                 rel: string,
     *                 href: string,
     *                 method: string,
     *             }>
     *         }>,
     *         captures?: array<int, array{
     *             id: string,
     *             invoice_id?: string,
     *             custom_id?: string,
     *             status: string,
     *             status_details?: array{
     *                 reason: string,
     *             },
     *             amount?: array{
     *                 value: string,
     *                 currency_code: string,
     *             },
     *             final_capture?: bool,
     *             seller_protection?: array{
     *                 status: string,
     *                 dispute_categories: array<int, string>,
     *             },
     *             seller_payable_breakdown?: array{
     *                 gross_amount?: array{
     *                     value: string,
     *                     currency_code: string,
     *                 },
     *                 paypal_fee?: array{
     *                     value: string,
     *                     currency_code: string,
     *                 },
     *                 paypal_fee_in_receivable_currency?: array{
     *                     value: string,
     *                     currency_code: string,
     *                 },
     *                 net_amount?: array{
     *                     value: string,
     *                     currency_code: string,
     *                 },
     *                 platform_fees?: array{
     *                     value: string,
     *                     currency_code: string,
     *                 },
     *                 receivable_amount?: array{
     *                      value: string,
     *                      currency_code: string,
     *                 },
     *                 exchange_rate?: array{
     *                      source_currency: string,
     *                      target_currency: string,
     *                      value: string,
     *                 },
     *             },
     *             processor_response?: array{
     *                 avs_code?: string,
     *                 cvv_code?: string,
     *                 response_code?: string,
     *                 payment_advice_code?: string,
     *             },
     *             network_transaction_reference?: array{
     *                 id: string,
     *                 date: string,
     *                 network: string,
     *                 acquirer_reference_number: string,
     *             },
     *             disbursement_mode?: string,
     *             create_time?: string,
     *             update_time?: string,
     *             links?: array<int, array{
     *                 rel: string,
     *                 href: string,
     *                 method: string,
     *             }>
     *         }>,
     *         refunds?: array<int, array{
     *             id: string,
     *             invoice_id?: string,
     *             custom_id?: string,
     *             status: string,
     *             status_details?: array{
     *                 reason: string,
     *             },
     *             amount?: array{
     *                 value: string,
     *                 currency_code: string,
     *             },
     *             note_to_payer?: string,
     *             seller_payable_breakdown?: array{
     *                 gross_amount?: array{
     *                     value: string,
     *                     currency_code: string,
     *                 },
     *                 paypal_fee?: array{
     *                     value: string,
     *                     currency_code: string,
     *                 },
     *                 paypal_fee_in_receivable_currency?: array{
     *                     value: string,
     *                     currency_code: string,
     *                 },
     *                 net_amount?: array{
     *                     value: string,
     *                     currency_code: string,
     *                 },
     *                 net_amount_in_receivable_currency?: array{
     *                     value: string,
     *                     currency_code: string,
     *                 },
     *                 platform_fees?: array{
     *                     value: string,
     *                     currency_code: string,
     *                 },
     *                 net_amount_breakdown?: array{
     *                      value: string,
     *                      currency_code: string,
     *                 },
     *                 total_refunded_amount?: array{
     *                      value: string,
     *                      currency_code: string,
     *                 },
     *             },
     *             create_time?: string,
     *             update_time?: string,
     *             links?: array<int, array{
     *                 rel: string,
     *                 href: string,
     *                 method: string,
     *             }>
     *         }>,
     *     },
     * }>
     */
    private $purchaseUnits;

    /**
     * @var array
     */
    private $links;

    /**
     * Constructor to initialize PayPalOrderResponse properties
     */
    public function __construct(
        string $id,
        string $status,
        string $intent,
        $payer,
        $paymentSource,
        array $purchaseUnits,
        array $links
    ) {
        $this->id = $id;
        $this->status = $status;
        $this->intent = $intent;
        $this->payer = $payer;
        $this->paymentSource = $paymentSource;
        $this->purchaseUnits = $purchaseUnits;
        $this->links = $links;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @return string
     */
    public function getIntent(): string
    {
        return $this->intent;
    }

    /**
     * @return string|null
     */
    public function getPayer()
    {
        return $this->payer;
    }

    /**
     * @return array|null
     */
    public function getPaymentSource()
    {
        return $this->paymentSource;
    }

    /**
     * @return array<int, array{
     *     reference_id?: string,
     *     invoice_id?: string,
     *     custom_id?: string,
     *     description?: string,
     *     items?: array<int, array<string, mixed>>,
     *     shipping?: array<string, mixed>,
     *     amount?: array{
     *         value: string,
     *         currency_code: string,
     *     },
     *     payments?: array{
     *         authorizations?: array<int, array{
     *             id: string,
     *             invoice_id?: string,
     *             custom_id?: string,
     *             status: string,
     *             amount: array{
     *                 value: string,
     *                 currency_code: string,
     *             },
     *             status_details?: array{
     *                 reason: string,
     *             },
     *             amount?: array{
     *                 value: string,
     *                 currency_code: string,
     *             },
     *             seller_protection?: array{
     *                 status: string,
     *                 dispute_categories: array<int, string>,
     *             },
     *             network_transaction_reference?: array{
     *                 id: string,
     *                 date: string,
     *                 network: string,
     *                 acquirer_reference_number: string,
     *             },
     *             create_time?: string,
     *             update_time?: string,
     *             expiration_time?: string,
     *             links?: array<int, array{
     *                 rel: string,
     *                 href: string,
     *                 method: string,
     *             }>
     *         }>,
     *         captures?: array<int, array{
     *             id: string,
     *             invoice_id?: string,
     *             custom_id?: string,
     *             status: string,
     *             status_details?: array{
     *                 reason: string,
     *             },
     *             amount?: array{
     *                 value: string,
     *                 currency_code: string,
     *             },
     *             final_capture?: bool,
     *             seller_protection?: array{
     *                 status: string,
     *                 dispute_categories: array<int, string>,
     *             },
     *             seller_payable_breakdown?: array{
     *                 gross_amount?: array{
     *                     value: string,
     *                     currency_code: string,
     *                 },
     *                 paypal_fee?: array{
     *                     value: string,
     *                     currency_code: string,
     *                 },
     *                 paypal_fee_in_receivable_currency?: array{
     *                     value: string,
     *                     currency_code: string,
     *                 },
     *                 net_amount?: array{
     *                     value: string,
     *                     currency_code: string,
     *                 },
     *                 platform_fees?: array{
     *                     value: string,
     *                     currency_code: string,
     *                 },
     *                 receivable_amount?: array{
     *                      value: string,
     *                      currency_code: string,
     *                 },
     *                 exchange_rate?: array{
     *                      source_currency: string,
     *                      target_currency: string,
     *                      value: string,
     *                 },
     *             },
     *             processor_response?: array{
     *                 avs_code?: string,
     *                 cvv_code?: string,
     *                 response_code?: string,
     *                 payment_advice_code?: string,
     *             },
     *             network_transaction_reference?: array{
     *                 id: string,
     *                 date: string,
     *                 network: string,
     *                 acquirer_reference_number: string,
     *             },
     *             disbursement_mode?: string,
     *             create_time?: string,
     *             update_time?: string,
     *             links?: array<int, array{
     *                 rel: string,
     *                 href: string,
     *                 method: string,
     *             }>
     *         }>,
     *         refunds?: array<int, array{
     *             id: string,
     *             invoice_id?: string,
     *             custom_id?: string,
     *             status: string,
     *             status_details?: array{
     *                 reason: string,
     *             },
     *             amount?: array{
     *                 value: string,
     *                 currency_code: string,
     *             },
     *             note_to_payer?: string,
     *             seller_payable_breakdown?: array{
     *                 gross_amount?: array{
     *                     value: string,
     *                     currency_code: string,
     *                 },
     *                 paypal_fee?: array{
     *                     value: string,
     *                     currency_code: string,
     *                 },
     *                 paypal_fee_in_receivable_currency?: array{
     *                     value: string,
     *                     currency_code: string,
     *                 },
     *                 net_amount?: array{
     *                     value: string,
     *                     currency_code: string,
     *                 },
     *                 net_amount_in_receivable_currency?: array{
     *                     value: string,
     *                     currency_code: string,
     *                 },
     *                 platform_fees?: array{
     *                     value: string,
     *                     currency_code: string,
     *                 },
     *                 net_amount_breakdown?: array{
     *                      value: string,
     *                      currency_code: string,
     *                 },
     *                 total_refunded_amount?: array{
     *                      value: string,
     *                      currency_code: string,
     *                 },
     *             },
     *             create_time?: string,
     *             update_time?: string,
     *             links?: array<int, array{
     *                 rel: string,
     *                 href: string,
     *                 method: string,
     *             }>
     *         }>,
     *     },
     * }>
     */
    public function getPurchaseUnits(): array
    {
        return $this->purchaseUnits;
    }

    /**
     * @return array
     */
    public function getLinks(): array
    {
        return $this->links;
    }

    /**
     * @return array|null
     */
    public function getAuthenticationResult()
    {
        $fundingSource = $this->getFundingSource();

        if ($fundingSource) {
            return $paymentSource[$fundingSource]['authentication_result'] ?? null;
        }

        return null;
    }

    /**
     * @return string|null
     */
    public function getFundingSource()
    {
        $paymentSource = $this->getPaymentSource();

        return $paymentSource !== null ? key($paymentSource) : null;
    }

    /**
     * @return array|null
     */
    public function getCapture()
    {
        return $this->getPurchaseUnits()[0]['payments']['captures'][0] ?? null;
    }

    /**
     * @return array<int, array{
     *     id?: string,
     *     status?: string,
     *     amount: array{
     *         value: string,
     *         currency_code: string,
     *     },
     * }>
     */
    public function getCaptures()
    {
        /** @var array<int, array{id?: string, status?: string, amount: array{value: string, currency_code: string}}> $captures */
        $captures = $this->getPurchaseUnits()[0]['payments']['captures'] ?? [];

        return $captures;
    }

    /**
     * @return array<int, array{
     *     amount: array{
     *         value: string,
     *         currency_code: string,
     *     },
     * }>|null
     */
    public function getRefunds()
    {
        /** @var array<int, array{amount: array{value: string, currency_code: string}}>|null $refunds */
        $refunds = $this->getPurchaseUnits()[0]['payments']['refunds'] ?? null;

        return $refunds;
    }

    /**
     * @return array{
     *     id: string,
     *     invoice_id?: string,
     *     custom_id?: string,
     *     status: string,
     *     amount: array{
     *         value: string,
     *         currency_code: string,
     *     },
     *     status_details?: array{
     *         reason: string,
     *     },
     *     amount?: array{
     *         value: string,
     *         currency_code: string,
     *     },
     *     seller_protection?: array{
     *         status: string,
     *         dispute_categories: array<int, string>,
     *     },
     *     network_transaction_reference?: array{
     *         id: string,
     *         date: string,
     *         network: string,
     *         acquirer_reference_number: string,
     *     },
     *     create_time?: string,
     *     update_time?: string,
     *     expiration_time?: string,
     *     links?: array<int, array{
     *         rel: string,
     *         href: string,
     *         method: string,
     *     }>
     * }|null
     */
    public function getAuthorization(): ?array
    {
        return $this->getPurchaseUnits()[0]['payments']['authorizations'][0] ?? null;
    }

    /**
     * @return array<int, array{
     *     id: string,
     *     invoice_id?: string,
     *     custom_id?: string,
     *     status: string,
     *     amount: array{
     *         value: string,
     *         currency_code: string,
     *     },
     *     status_details?: array{
     *         reason: string,
     *     },
     *     amount?: array{
     *         value: string,
     *         currency_code: string,
     *     },
     *     seller_protection?: array{
     *         status: string,
     *         dispute_categories: array<int, string>,
     *     },
     *     network_transaction_reference?: array{
     *         id: string,
     *         date: string,
     *         network: string,
     *         acquirer_reference_number: string,
     *     },
     *     create_time?: string,
     *     update_time?: string,
     *     expiration_time?: string,
     *     links?: array<int, array{
     *         rel: string,
     *         href: string,
     *         method: string,
     *     }>
     * }>
     */
    public function getAuthorizations(): array
    {
        $purchaseUnits = $this->getPurchaseUnits();
        if (empty($purchaseUnits)) {
            throw new \RuntimeException('No purchase units found in the order response.');
        }
        if (1 < count($purchaseUnits)) {
            throw new \RuntimeException('More than one purchase unit found in the order response.');
        }
        if (!isset($purchaseUnits[0]['payments']['authorizations'])) {
            return [];
        }

        return $this->getPurchaseUnits()[0]['payments']['authorizations'];
    }

    /**
     * @return array|null
     */
    public function getCard()
    {
        return $this->getPaymentSource()['card'] ?? null;
    }

    /**
     * @return string|null
     */
    public function getLiabilityShift()
    {
        return $this->getAuthenticationResult()['liability_shift'] ?? null;
    }

    /**
     * @return array|null
     */
    public function get3dSecure()
    {
        return $this->getAuthenticationResult()['three_d_secure'] ?? null;
    }

    /**
     * @return string|null
     */
    public function get3dSecureAuthenticationStatus()
    {
        return $this->get3dSecure()['authentication_status'] ?? null;
    }

    /**
     * @return string|null
     */
    public function get3dSecureEnrollmentStatus()
    {
        return $this->get3dSecure()['enrollment_status'] ?? null;
    }

    /**
     * @return array
     */
    public function getOrderAmount(): array
    {
        return $this->getPurchaseUnits()[0]['amount'] ?? [];
    }

    /**
     * @return string|null
     */
    public function getOrderAmountValue()
    {
        return $this->getPurchaseUnits()[0]['amount']['value'] ?? null;
    }

    /**
     * @return array|null
     */
    public function getVault()
    {
        $paymentSourceArray = $this->getPaymentSource();

        if (!is_array($paymentSourceArray)) {
            return null;
        }

        $paymentSource = key($paymentSourceArray);

        if ($paymentSource) {
            return $paymentSourceArray[$paymentSource]['attributes']['vault'] ?? null;
        }

        return null;
    }

    /**
     * @return string|null
     */
    public function getCustomerId()
    {
        $paymentSourceArray = $this->getPaymentSource();

        if (!is_array($paymentSourceArray)) {
            return null;
        }

        $paymentSource = key($paymentSourceArray);

        if ($paymentSource) {
            return $paymentSourceArray[$paymentSource]['attributes']['vault']['customer']['id'] ?? null;
        }

        return null;
    }

    /**
     * @return string
     */
    public function getTransactionStatus(): string
    {
        return $this->getPurchaseUnits()[0]['payments']['captures'][0]['status'] ?? '';
    }

    /**
     * @return string
     */
    public function getTransactionId(): string
    {
        return $this->getPurchaseUnits()[0]['payments']['captures'][0]['id'] ?? '';
    }

    /**
     * @return string
     */
    public function getTotalAmount(): string
    {
        return $this->getPurchaseUnits()[0]['payments']['captures'][0]['amount']['value'] ?? '';
    }

    /**
     * @return string
     */
    public function getCurrencyCode(): string
    {
        return $this->getPurchaseUnits()[0]['payments']['captures'][0]['amount']['currency_code'] ?? '';
    }

    /**
     * @return string
     */
    public function getApprovalLink(): string
    {
        foreach ($this->getLinks() as $link) {
            if ('approve' === $link['rel']) {
                return $link['href'];
            }
        }

        return '';
    }

    /**
     * @return array
     */
    public function getItems(): array
    {
        return $this->getPurchaseUnits()[0]['items'] ?? [];
    }

    /**
     * @return string
     */
    public function getPayerActionLink(): string
    {
        foreach ($this->getLinks() as $link) {
            if ('payer-action' === $link['rel']) {
                return $link['href'];
            }
        }

        return '';
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [
            'id' => $this->id,
            'status' => $this->status,
            'intent' => $this->intent,
            'purchase_units' => $this->purchaseUnits,
        ];

        if ($this->paymentSource !== null) {
            $data['payment_source'] = $this->paymentSource;
        }

        return $data;
    }
}

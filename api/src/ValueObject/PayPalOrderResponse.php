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
     * @var array
     */
    private $purchaseUnits;

    /**
     * @var array
     */
    private $links;

    /**
     * @var string
     */
    private $createTime;

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
        array $links,
        string $createTime
    ) {
        $this->id = $id;
        $this->status = $status;
        $this->intent = $intent;
        $this->payer = $payer;
        $this->paymentSource = $paymentSource;
        $this->purchaseUnits = $purchaseUnits;
        $this->links = $links;
        $this->createTime = $createTime;
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
     * @return array
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
     * @return string
     */
    public function getCreateTime(): string
    {
        return $this->createTime;
    }

    /**
     * @return array|null
     */
    public function getAuthenticationResult()
    {
        $fundingSource = key($this->getPaymentSource());

        return $this->getPaymentSource()[$fundingSource]['authentication_result'] ?? null;
    }

    /**
     * @return string|null
     */
    public function getFundingSource()
    {
        return key($this->getPaymentSource());
    }

    /**
     * @return array|null
     */
    public function getCapture()
    {
        return $this->getPurchaseUnits()[0]['payments']['captures'][0] ?? null;
    }

    /**
     * @return array|null
     */
    public function getRefunds()
    {
        return $this->getPurchaseUnits()[0]['payments']['refunds'] ?? null;
    }

    /**
     * @return array|null
     */
    public function getAuthorization()
    {
        return $this->getPurchaseUnits()[0]['payments']['authorizations'][0] ?? null;
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
        return $this->getPaymentSource()[key($this->getPaymentSource())]['attributes']['vault'] ?? null;
    }

    /**
     * @return string|null
     */
    public function getCustomerId()
    {
        return $this->getPaymentSource()[key($this->getPaymentSource())]['attributes']['vault']['customer']['id'] ?? null;
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
}

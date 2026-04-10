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

namespace PsCheckout\Core\WebhookDispatcher\ValueObject;

use PsCheckout\Core\Webhook\Configuration\WebhookCategoryConfiguration;
use PsCheckout\Core\Webhook\Configuration\WebhookEventTypeConfiguration;

class DispatchWebhookRequest
{
    /**
     * @var string
     */
    private $webhookId;

    /**
     * @var string
     */
    private $shopId;

    /**
     * @var array
     */
    private $resource;

    /**
     * @var string
     */
    private $eventType;

    /**
     * @var string
     */
    private $category;

    /**
     * @var string|null
     */
    private $summary;

    /**
     * @var string|null
     */
    private $orderId;

    /**
     * @var string|null
     */
    private $merchantId;

    /**
     * @var string|null
     */
    private $firebaseId;

    /**
     * @var string|null
     */
    private $eventStream;

    /**
     * @var string|null
     */
    private $eventNumber;

    /**
     * DispatchWebhookRequest constructor.
     *
     * @param string $webhookId
     * @param string $shopId
     * @param array $resource
     * @param string $eventType
     * @param string $category
     * @param string|null $summary
     * @param string|null $orderId
     * @param string|null $eventStream
     * @param string|null $eventNumber
     * @param string|null $merchantId
     * @param string|null $firebaseId
     */
    public function __construct(
        string $webhookId,
        string $shopId,
        array $resource,
        string $eventType,
        string $category,
        ?string $orderId = null,
        ?string $summary = null,
        ?string $eventStream = null,
        ?string $eventNumber = null,
        ?string $merchantId = null,
        ?string $firebaseId = null
    ) {
        $this->webhookId = $webhookId;
        $this->shopId = $shopId;
        $this->resource = $resource;
        $this->eventType = $eventType;
        $this->category = $category;
        $this->orderId = $orderId;
        $this->summary = $summary;
        $this->eventStream = $eventStream;
        $this->eventNumber = $eventNumber;
        $this->merchantId = $merchantId;
        $this->firebaseId = $firebaseId;
    }

    /**
     * @return array
     */
    public function getResource(): array
    {
        return $this->resource;
    }

    /**
     * @return string
     */
    public function getEventType(): string
    {
        return $this->eventType;
    }

    /**
     * @return string
     */
    public function getCategory(): string
    {
        return $this->category;
    }

    /**
     * @return string|null
     */
    public function getSummary(): ?string
    {
        return $this->summary;
    }

    /**
     * @return string|null
     */
    public function getOrderId(): ?string
    {
        return $this->orderId;
    }

    /**
     * @return string
     */
    public function getShopId(): string
    {
        return $this->shopId;
    }

    /**
     * @return string|null
     */
    public function getMerchantId(): ?string
    {
        return $this->merchantId;
    }

    /**
     * @return string|null
     */
    public function getFirebaseId(): ?string
    {
        return $this->firebaseId;
    }

    /**
     * @return string|null
     */
    public function getEventStream(): ?string
    {
        return $this->eventStream;
    }

    /**
     * @return string|null
     */
    public function getEventNumber(): ?string
    {
        return $this->eventNumber;
    }

    /**
     * Creates a new instance of DispatchWebhookRequest from request data.
     *
     * @param array{
     *     webhookId: string,
     *     shopId: string,
     *     resource: array<string, mixed>,
     *     eventType: string,
     *     orderId?: string|null,
     *     summary?: string|null
     * } $bodyValues
     *
     * @return DispatchWebhookRequest
     */
    public static function createFromRequest(array $bodyValues): self
    {
        $mappedEventType = (string) WebhookEventTypeConfiguration::getMappedEventType((string) $bodyValues['eventType']);

        /** @var array<string, mixed> $resource */
        $resource = $bodyValues['resource'];

        return new self(
            (string) $bodyValues['webhookId'],
            (string) $bodyValues['shopId'],
            $resource,
            $mappedEventType,
            WebhookCategoryConfiguration::SVIX,
            $bodyValues['orderId'] ?? null,
            $bodyValues['summary'] ?? null
        );
    }

    /**
     * Creates a new instance of DispatchWebhookRequest from maasland request data.
     *
     * @param array{
     *      webhookId: string,
     *      resource: array<string, mixed>,
     *      eventType: string,
     *      eventStream: string,
     *      eventNumber: string,
     *      category: string,
     *      summary: string|null,
     *      orderId: string|null
     *  } $bodyValues
     * @param array{
     *     shopId: string,
     *     merchantId: string,
     *     firebaseId: string
     * } $headerValues
     *
     * @return DispatchWebhookRequest
     */
    public static function createFromMaaslandRequest(array $bodyValues, array $headerValues): self
    {
        return new self(
            (string) $bodyValues['webhookId'],
            (string) $headerValues['shopId'],
            (array) $bodyValues['resource'],
            (string) $bodyValues['eventType'],
            (string) $bodyValues['category'],
            $bodyValues['orderId'] ?? null,
            $bodyValues['summary'] ?? null,
            (string) $bodyValues['eventStream'],
            (string) $bodyValues['eventNumber'],
            (string) $headerValues['merchantId'],
            (string) $headerValues['firebaseId']
        );
    }

    /**
     * Converts the object to an array, excluding null values.
     *
     * @return array
     */
    public function toArray(): array
    {
        return array_filter([
            'webhookId' => $this->webhookId,
            'shopId' => $this->shopId,
            'resource' => $this->resource,
            'eventType' => $this->eventType,
            'category' => $this->category,
            'summary' => $this->summary,
            'orderId' => $this->orderId,
            'eventStream' => $this->eventStream,
            'eventNumber' => $this->eventNumber,
            'merchantId' => $this->merchantId,
            'firebaseId' => $this->firebaseId,
        ], function ($value) {
            return $value !== null;
        });
    }
}

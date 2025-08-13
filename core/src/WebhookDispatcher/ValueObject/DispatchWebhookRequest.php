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

class DispatchWebhookRequest
{
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
     * @var string
     */
    private $shopId;

    /**
     * @var string
     */
    private $merchantId;

    /**
     * @var string
     */
    private $firebaseId;

    /**
     * DispatchWebhookRequest constructor.
     *
     * @param array $resource
     * @param string $eventType
     * @param string $category
     * @param string|null $summary
     * @param string|null $orderId
     * @param string $shopId
     * @param string $merchantId
     * @param string $firebaseId
     */
    public function __construct(
        array $resource,
        string $eventType,
        string $category,
        $summary,
        $orderId,
        string $shopId,
        string $merchantId,
        string $firebaseId
    ) {
        $this->resource = $resource;
        $this->eventType = $eventType;
        $this->category = $category;
        $this->summary = $summary;
        $this->orderId = $orderId;
        $this->shopId = $shopId;
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
    public function getSummary()
    {
        return $this->summary;
    }

    /**
     * @return string|null
     */
    public function getOrderId()
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
     * @return string
     */
    public function getMerchantId(): string
    {
        return $this->merchantId;
    }

    /**
     * @return string
     */
    public function getFirebaseId(): string
    {
        return $this->firebaseId;
    }

    /**
     * Creates a new instance of DispatchWebhookRequest from request data.
     *
     * @param array $bodyValues
     * @param array $headerValues
     *
     * @return DispatchWebhookRequest
     */
    public static function createFromRequest(array $bodyValues, array $headerValues): self
    {
        return new self(
            (array) $bodyValues['resource'],
            (string) $bodyValues['eventType'],
            (string) $bodyValues['category'],
            $bodyValues['summary'] ?? null,
            $bodyValues['orderId'] ?? null,
            (string) $headerValues['Shop-Id'],
            (string) $headerValues['Merchant-Id'],
            (string) $headerValues['Psx-Id']
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
            'resource' => $this->resource,
            'eventType' => $this->eventType,
            'category' => $this->category,
            'summary' => $this->summary,
            'orderId' => $this->orderId,
            'shopId' => $this->shopId,
            'merchantId' => $this->merchantId,
            'firebaseId' => $this->firebaseId,
        ], function ($value) {
            return $value !== null;
        });
    }
}

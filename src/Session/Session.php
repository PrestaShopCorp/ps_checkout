<?php

/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 */

namespace PrestaShop\Module\PrestashopCheckout\Session;

class Session
{
    /**
     * @var string
     */
    private $correlationId;

    /**
     * @var int
     */
    private $userId;

    /**
     * @var int
     */
    private $shopId;

    /**
     * @var int
     */
    private $isClosed;

    /**
     * @var string
     */
    private $authToken;

    /**
     * @var string
     */
    private $status;

    /**
     * @var string
     */
    private $createdAt;

    /**
     * @var string
     */
    private $updatedAt;

    /**
     * @var string
     */
    private $closedAt;

    /**
     * @var string
     */
    private $expiresAt;

    /**
     * @var bool
     */
    private $isSseOpened;

    /**
     * @var string
     */
    private $data;

    /**
     * @param array $session
     *
     * @return void
     */
    public function __construct(array $session)
    {
        $this->correlationId = $session['correlation_id'];
        $this->userId = $session['user_id'];
        $this->shopId = $session['shop_id'];
        $this->isClosed = $session['is_closed'];
        $this->authToken = $session['auth_token'];
        $this->status = $session['status'];
        $this->createdAt = $session['created_at'];
        $this->updatedAt = $session['updated_at'];
        $this->closedAt = $session['closed_at'];
        $this->expiresAt = $session['expires_at'];
        $this->isSseOpened = $session['is_sse_opened'];
        $this->data = $session['data'];
    }

    /**
     * Get the session correlation ID
     *
     * @return string
     */
    public function getCorrelationId()
    {
        return $this->correlationId;
    }

    /**
     * Get the session user ID
     *
     * @return int
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Get the session shop ID
     *
     * @return int
     */
    public function getShopId()
    {
        return $this->shopId;
    }

    /**
     * Get the session closing state
     *
     * @return int
     */
    public function getIsClosed()
    {
        return $this->isClosed;
    }

    /**
     * Get the session auth token
     *
     * @return string
     */
    public function getAuthToken()
    {
        return $this->authToken;
    }

    /**
     * Get the session status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Get the session creation date
     *
     * @return string
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Get the session update date
     *
     * @return string
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Get the session closing date
     *
     * @return string
     */
    public function getClosedAt()
    {
        return $this->closedAt;
    }

    /**
     * Get the session expiration date
     *
     * @return string|null
     */
    public function getExpiresAt()
    {
        return $this->expiresAt;
    }

    /**
     * Get the session SSE opening state
     *
     * @return bool
     */
    public function getIsSseOpened()
    {
        return $this->isSseOpened;
    }

    /**
     * Get the session data
     *
     * @return string|null
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Set the session auth token
     *
     * @param string $authToken
     *
     * @return void
     */
    public function setAuthToken($authToken)
    {
        $this->authToken = $authToken;
    }

    /**
     * Set the session status
     *
     * @param string $status
     *
     * @return void
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * Set the session update date
     *
     * @param string $updatedAt
     *
     * @return void
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * Set the session closing date
     *
     * @param string $closedAt
     *
     * @return void
     */
    public function setClosedAt($closedAt)
    {
        $this->closedAt = $closedAt;
    }

    /**
     * Set the session expiration date
     *
     * @param string $expiresAt
     *
     * @return void
     */
    public function setExpiresAt($expiresAt)
    {
        $this->expiresAt = $expiresAt;
    }

    /**
     * Set the session SSE opening state
     *
     * @param bool $isSSEOpened
     *
     * @return void
     */
    public function setIsSseOpened($isSseOpened)
    {
        $this->isSseOpened = $isSseOpened;
    }

    /**
     * Set the session data
     *
     * @param string $data
     *
     * @return void
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * Convert the session to array
     *
     * @param bool $dataIsArray When this parameter is set to true, data will be an array
     *
     * @return array
     */
    public function toArray($dataIsArray = false)
    {
        return [
            'correlation_id' => $this->correlationId,
            'user_id' => $this->userId,
            'shop_id' => $this->shopId,
            'is_closed' => $this->isClosed,
            'auth_token' => $this->authToken,
            'status' => $this->status,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
            'closed_at' => $this->closedAt,
            'expires_at' => $this->expiresAt,
            'is_sse_opened' => $this->isSseOpened,
            'data' => json_decode($this->data, $dataIsArray),
        ];
    }
}

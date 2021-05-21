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
     * @var int
     */
    private $userId;

    /**
     * @var int
     */
    private $shopId;

    /**
     * @var string
     */
    private $isClosed;

    /**
     * @var string
     */
    private $correlationId;

    /**
     * @var string
     */
    private $status;

    /**
     * @var string
     */
    private $data;

    /**
     * @var string
     */
    private $createdAt;

    /**
     * @var string
     */
    private $expiresAt;

    /**
     * @var string
     */
    private $authToken;

    /**
     * @var string
     */
    private $updatedAt;

    /**
     * @var string
     */
    private $closedAt;

    /**
     * @var int
     */
    private $isSSEOpened;

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
        $this->data = $session['data'];
        $this->createdAt = $session['created_at'];
        $this->updatedAt = $session['updated_at'];
        $this->closedAt = $session['closed_at'];
        $this->expiresAt = $session['expires_at'];
        $this->isSSEOpened = (int) $session['is_sse_opened'];
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
     * Get the session process type
     *
     * @return string
     */
    public function getIsClosed()
    {
        return $this->isClosed;
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
     * Get the session status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
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
     * Get the session creation date
     *
     * @return string
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
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
     * Update the session expiration date
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
     * @return string
     */
    public function getAuthToken()
    {
        return $this->authToken;
    }

    /**
     * @param string $authToken
     */
    public function setAuthToken($authToken)
    {
        $this->authToken = $authToken;
    }

    /**
     * @return string
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param string $updatedAt
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * @return string
     */
    public function getClosedAt()
    {
        return $this->closedAt;
    }

    /**
     * @param string $closedAt
     */
    public function setClosedAt($closedAt)
    {
        $this->closedAt = $closedAt;
    }

    /**
     * @return int
     */
    public function getIsSSEOpened()
    {
        return $this->isSSEOpened;
    }

    /**
     * @param int $isSSEOpened
     */
    public function setIsSSEOpened($isSSEOpened)
    {
        $this->isSSEOpened = $isSSEOpened;
    }

    /**
     * Convert the session to array
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'correlation_id' => $this->correlationId,
            'user_id' => $this->userId,
            'shop_id' => $this->shopId,
            'is_closed' => $this->isClosed,
            'auth_token' => $this->authToken,
            'status' => $this->status,
            'data' => $this->data,
            'created_at' => $this->createdAt,
            'updated_at' => $this->expiresAt,
            'closed_at' => $this->closedAt,
            'expires_at' => $this->expiresAt,
            'is_sse_opened' => $this->isSSEOpened,
        ];
    }
}

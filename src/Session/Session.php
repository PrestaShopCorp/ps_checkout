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
    private $processType;

    /**
     * @var string|null
     */
    private $accountId;

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
    private $creationDate;

    /**
     * @var string
     */
    private $expirationDate;

    /**
     * @param array $session
     *
     * @return void
     */
    public function __construct(array $session)
    {
        $this->userId = $session['user_id'];
        $this->shopId = $session['shop_id'];
        $this->processType = $session['process_type'];
        $this->accountId = $session['account_id'];
        $this->correlationId = $session['correlation_id'];
        $this->status = $session['status'];
        $this->data = $session['data'];
        $this->creationDate = $session['creation_date'];
        $this->expirationDate = $session['expiration_date'];
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
    public function getProcessType()
    {
        return $this->processType;
    }

    /**
     * Get the session account ID
     *
     * @return string
     */
    public function getAccountId()
    {
        return $this->accountId;
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
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * Get the session expiration date
     *
     * @return string|null
     */
    public function getExpirationDate()
    {
        return $this->expirationDate;
    }

    /**
     * Set the session account ID
     *
     * @param string $accountId
     *
     * @return void
     */
    public function setAccountId($accountId)
    {
        $this->accountId = $accountId;
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
     * @param string $expirationDate
     *
     * @return void
     */
    public function setExpirationDate($expirationDate)
    {
        $this->expirationDate = $expirationDate;
    }

    /**
     * Convert the session to array
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'user_id' => $this->userId,
            'shop_id' => $this->shopId,
            'process_type' => $this->processType,
            'account_id' => $this->accountId,
            'correlation_id' => $this->correlationId,
            'status' => $this->status,
            'data' => $this->data,
            'creation_date' => $this->creationDate,
            'expiration_date' => $this->expirationDate,
        ];
    }
}

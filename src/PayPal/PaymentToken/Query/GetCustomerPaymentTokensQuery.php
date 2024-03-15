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

namespace PrestaShop\Module\PrestashopCheckout\PayPal\PaymentToken\Query;

use PrestaShop\Module\PrestashopCheckout\Customer\ValueObject\CustomerId;

class GetCustomerPaymentTokensQuery
{
    /**
     * @var CustomerId
     */
    private $customerId;

    /**
     * @var int
     */
    private $pageSize;

    /**
     * @var int
     */
    private $pageNumber;

    /**
     * @var bool
     */
    private $isTotalCountRequired;

    /**
     * @param CustomerId $customerId
     * @param int $pageSize
     * @param int $pageNumber
     * @param bool $isTotalCountRequired
     */
    public function __construct(CustomerId $customerId, $pageSize, $pageNumber, $isTotalCountRequired = false)
    {
        $this->customerId = $customerId;
        $this->pageSize = $pageSize;
        $this->pageNumber = $pageNumber;
        $this->isTotalCountRequired = $isTotalCountRequired;
    }

    /**
     * @return CustomerId
     */
    public function getCustomerId()
    {
        return $this->customerId;
    }

    /**
     * @return int
     */
    public function getPageSize()
    {
        return $this->pageSize;
    }

    /**
     * @return int
     */
    public function getPageNumber()
    {
        return $this->pageNumber;
    }

    /**
     * @return bool
     */
    public function isTotalCountRequired()
    {
        return $this->isTotalCountRequired;
    }
}

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

namespace PrestaShop\Module\PrestashopCheckout\PayPal\PaymentToken\Event;

use PrestaShop\Module\PrestashopCheckout\Event\Event;

class PaymentTokenEvent extends Event
{
    /** @var int */
    private $customerId;

    /** @var int */
    private $shopId;

    /** @var array */
    private $resource;

    /**
     * @param array $resource
     */
    public function __construct($resource, $customerId, $shopId)
    {
        $this->resource = $resource;
        $this->shopId = $shopId;
        $this->customerId = $customerId;
    }

    /**
     * @return array
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * @return int
     */
    public function getCustomerId()
    {
        return $this->customerId;
    }

    /**
     * @return int
     */
    public function getShopId()
    {
        return $this->shopId;
    }
}

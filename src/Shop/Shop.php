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

namespace PrestaShop\Module\PrestashopCheckout\Shop;

use PrestaShop\Module\PrestashopCheckout\Exception\ShopException;

/**
 * Get the shop context
 */
class Shop
{
    /** @var int */
    private $id;
    /** @var string */
    private $returnUrl;
    /** @var string */
    private $cancelUrl;

    /**
     * @param $id
     * @param $returnUrl
     * @param $cancelUrl
     *
     * @throws ShopException
     */
    public function __construct($id, $returnUrl, $cancelUrl)
    {
        $this->id = $this->assertShopIdIsValid($id);
        $this->returnUrl = $this->assertShopReturnUrlIsValid($returnUrl);
        $this->cancelUrl = $this->assertShopCancelUrlIsValid($cancelUrl);
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getReturnUrl()
    {
        return $this->returnUrl;
    }

    /**
     * @param string $returnUrl
     */
    public function setReturnUrl($returnUrl)
    {
        $this->returnUrl = $returnUrl;
    }

    /**
     * @return string
     */
    public function getCancelUrl()
    {
        return $this->cancelUrl;
    }

    /**
     * @param string $cancelUrl
     */
    public function setCancelUrl($cancelUrl)
    {
        $this->cancelUrl = $cancelUrl;
    }

    private function assertShopIdIsValid($id)
    {
        if (!is_int($id)) {
            throw new ShopException(sprintf('ID is not an int (%s)', gettype($id)), ShopException::WRONG_TYPE_ID);
        }

        return $id;
    }

    private function assertShopReturnUrlIsValid($returnUrl)
    {
        if (!is_string($returnUrl)) {
            throw new ShopException(sprintf('ReturnUrl is not a string (%s)', gettype($returnUrl)), ShopException::WRONG_TYPE_RETURN_URL);
        }
        if (!filter_var($returnUrl, FILTER_VALIDATE_URL)) {
            throw new ShopException('ReturnUrl is not valid url', ShopException::INVALID_RETURN_URL);
        }

        return $returnUrl;
    }

    private function assertShopCancelUrlIsValid($cancelUrl)
    {
        if (!is_string($cancelUrl)) {
            throw new ShopException(sprintf('CancelUrl is not a string (%s)', gettype($cancelUrl)), ShopException::WRONG_TYPE_CANCEL_URL);
        }
        if (!filter_var($cancelUrl, FILTER_VALIDATE_URL)) {
            throw new ShopException('CancelUrl is not valid url', ShopException::INVALID_CANCEL_URL);
        }

        return $cancelUrl;
    }
}

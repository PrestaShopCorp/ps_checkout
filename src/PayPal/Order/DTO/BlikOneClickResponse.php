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

namespace PrestaShop\Module\PrestashopCheckout\PayPal\Order\DTO;

class BlikOneClickResponse
{
    /**
     * The merchant generated, unique reference serving as a primary identifier for accounts connected between Blik and a merchant.
     *
     * @var string|null
     */
    protected $consumer_reference;

    /**
     * Constructor
     *
     * @param mixed[] $data Associated array of property values initializing the model
     */
    public function __construct(array $data = null)
    {
        $this->consumer_reference = isset($data['consumer_reference']) ? $data['consumer_reference'] : null;
    }

    /**
     * Gets consumer_reference.
     *
     * @return string|null
     */
    public function getConsumerReference()
    {
        return $this->consumer_reference;
    }

    /**
     * Sets consumer_reference.
     *
     * @param string|null $consumer_reference the merchant generated, unique reference serving as a primary identifier for accounts connected between Blik and a merchant
     *
     * @return $this
     */
    public function setConsumerReference($consumer_reference = null)
    {
        $this->consumer_reference = $consumer_reference;

        return $this;
    }
}

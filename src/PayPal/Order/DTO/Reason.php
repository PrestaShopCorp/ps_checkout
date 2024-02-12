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

class Reason
{
    /**
     * The reason why the captured payment status is &#x60;PENDING&#x60; or &#x60;DENIED&#x60;.
     *
     * @var string|null
     */
    protected $reason;

    /**
     * Constructor
     *
     * @param mixed[] $data Associated array of property values initializing the model
     */
    public function __construct(array $data = null)
    {
        $this->reason = isset($data['reason']) ? $data['reason'] : null;
    }

    /**
     * Gets reason.
     *
     * @return string|null
     */
    public function getReason()
    {
        return $this->reason;
    }

    /**
     * Sets reason.
     *
     * @param string|null $reason the reason why the captured payment status is `PENDING` or `DENIED`
     *
     * @return $this
     */
    public function setReason($reason = null)
    {
        $this->reason = $reason;

        return $this;
    }
}

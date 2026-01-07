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

namespace PsCheckout\Api\Dto\PayPal;

/**
 * Information used to pay using BLIK level_0 flow.
 */
class BlikLevel0PaymentObject
{
    /**
     * @var string
     */
    private $authCode;

    /**
     * @param string $authCode
     */
    public function __construct(string $authCode)
    {
        $this->authCode = $authCode;
    }

    /**
     * Returns Auth Code.
     * The 6-digit code used to authenticate a consumer within BLIK.
     */
    public function getAuthCode(): string
    {
        return $this->authCode;
    }

    /**
     * Sets Auth Code.
     * The 6-digit code used to authenticate a consumer within BLIK.
     *
     * @required
     * @maps auth_code
     * @return self
     */
    public function setAuthCode(string $authCode): self
    {
        $this->authCode = $authCode;

        return $this;
    }
}

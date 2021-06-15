<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */

namespace PrestaShop\Module\PrestashopCheckout\Configuration;

use PrestaShop\Module\PrestashopCheckout\Api\Firebase\Token;
use PrestaShop\Module\PrestashopCheckout\Configuration\PrestaShopConfiguration;
use PrestaShop\Module\PrestashopCheckout\Entity\PsAccount;

class PrestashopCheckoutConfiguration
{
    /**
     * @var \PrestaShop\Module\PrestashopCheckout\Configuration\PrestaShopConfiguration
     */
    private $prestashopConfiguration;

    /**
     * @param PrestaShopConfiguration $prestashopConfiguration
     */
    public function __construct(PrestaShopConfiguration $prestashopConfiguration)
    {
        $this->prestashopConfiguration = $prestashopConfiguration;
    }

    public function getFirebase()
    {
        $token = new Token();

        return [
            'email' => $this->prestashopConfiguration->get(PsAccount::PS_PSX_FIREBASE_EMAIL),
            'token' => $token->getToken(),
            'accountId' => $this->prestashopConfiguration->get(PsAccount::PS_PSX_FIREBASE_LOCAL_ID),
            'refreshToken' => $this->prestashopConfiguration->get(PsAccount::PS_PSX_FIREBASE_REFRESH_TOKEN),
        ];
    }

    public function getShopData()
    {
        return [
            'psxForm' => $this->prestashopConfiguration->get(PsAccount::PS_CHECKOUT_PSX_FORM),
        ];
    }
}

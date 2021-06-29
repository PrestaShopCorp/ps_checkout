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

namespace PrestaShop\Module\PrestashopCheckout;

use PrestaShop\Module\PrestashopCheckout\Configuration\PrestaShopConfiguration;
use PrestaShop\Module\PrestashopCheckout\Entity\PaypalAccount;
use PrestaShop\Module\PrestashopCheckout\Entity\PsAccount;

/**
 * Not really an entity.
 * Define and manage data regarding paypal account
 */
class PersistentConfiguration
{
    /**
     * @var PrestaShopConfiguration
     */
    private $configuration;

    public function __construct(PrestaShopConfiguration $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * Function used to reset the PayPalField to LogOut the user
     *
     * @return bool
     */
    public function resetPayPalAccount()
    {
        try {
            $this->configuration->set(PaypalAccount::PS_CHECKOUT_PAYPAL_ID_MERCHANT, '');
            $this->configuration->set(PaypalAccount::PS_CHECKOUT_PAYPAL_EMAIL_MERCHANT, '');
            $this->configuration->set(PaypalAccount::PS_CHECKOUT_PAYPAL_COUNTRY_MERCHANT, '');
            $this->configuration->set(PaypalAccount::PS_CHECKOUT_PAYPAL_EMAIL_STATUS, '');
            $this->configuration->set(PaypalAccount::PS_CHECKOUT_PAYPAL_PAYMENT_STATUS, '');
            $this->configuration->set(PaypalAccount::PS_CHECKOUT_CARD_HOSTED_FIELDS_STATUS, '');
        } catch (\Exception $exception) {
            return false;
        }

        return true;
    }

    /**
     * Save / update paypal account in database
     *
     * @param PaypalAccount $paypalAccount
     *
     * @return bool
     */
    public function savePaypalAccount(PaypalAccount $paypalAccount)
    {
        try {
            $this->configuration->set(PaypalAccount::PS_CHECKOUT_PAYPAL_ID_MERCHANT, $paypalAccount->getMerchantId());
            $this->configuration->set(PaypalAccount::PS_CHECKOUT_PAYPAL_EMAIL_MERCHANT, $paypalAccount->getEmail());
            $this->configuration->set(PaypalAccount::PS_CHECKOUT_PAYPAL_COUNTRY_MERCHANT, $paypalAccount->getMerchantCountry());
            $this->configuration->set(PaypalAccount::PS_CHECKOUT_PAYPAL_EMAIL_STATUS, $paypalAccount->getEmailIsVerified());
            $this->configuration->set(PaypalAccount::PS_CHECKOUT_PAYPAL_PAYMENT_STATUS, $paypalAccount->getPaypalPaymentStatus());
            $this->configuration->set(PaypalAccount::PS_CHECKOUT_CARD_HOSTED_FIELDS_STATUS, $paypalAccount->getCardPaymentStatus());
        } catch (\Exception $exception) {
            return false;
        }

        return true;
    }

    /**
     * Function used to reset the PS account to LogOut the user
     *
     * @return bool
     */
    public function resetPsAccount()
    {
        try {
            $this->configuration->set(PsAccount::PS_PSX_FIREBASE_EMAIL, '');
            $this->configuration->set(PsAccount::PS_PSX_FIREBASE_ID_TOKEN, '');
            $this->configuration->set(PsAccount::PS_PSX_FIREBASE_LOCAL_ID, '');
            $this->configuration->set(PsAccount::PS_PSX_FIREBASE_REFRESH_TOKEN, '');
            $this->configuration->set(PsAccount::PS_CHECKOUT_PSX_FORM, '');
        } catch (\Exception $exception) {
            return false;
        }

        return true;
    }

    /**
     * Save / update ps account in database
     *
     * @param PsAccount $psAccount
     *
     * @return bool
     */
    public function savePsAccount(PsAccount $psAccount)
    {
        // Generate a new PS Checkout shop UUID if PS Account and Checkout shop UUID are identicals
        $psContext = new \PrestaShop\Module\PrestashopCheckout\Context\PrestaShopContext();
        $shopUuidManager = new \PrestaShop\Module\PrestashopCheckout\ShopUuidManager();
        $shopId = (int) $psContext->getShopId();
        $shopUuid = $shopUuidManager->getForShop($shopId);
        $psAccountsShopUuid = $this->configuration->get('PSX_UUID_V4');

        if (!$shopUuid || $shopUuid === $psAccountsShopUuid) {
            $this->configuration->set(PsAccount::PS_CHECKOUT_SHOP_UUID_V4, '');
            $shopUuidManager->generateForShop($shopId);
            $shopUuid = $shopUuidManager->getForShop($shopId);
        }

        try {
            $this->configuration->set(PsAccount::PS_PSX_FIREBASE_EMAIL, $psAccount->getEmail());
            $this->configuration->set(PsAccount::PS_PSX_FIREBASE_ID_TOKEN, $psAccount->getIdToken());
            $this->configuration->set(PsAccount::PS_PSX_FIREBASE_LOCAL_ID, $psAccount->getLocalId());
            $this->configuration->set(PsAccount::PS_PSX_FIREBASE_REFRESH_TOKEN, $psAccount->getRefreshToken());
            $this->configuration->set(PsAccount::PS_CHECKOUT_PSX_FORM, $psAccount->getPsxForm());
            $this->configuration->set(PsAccount::PS_CHECKOUT_SHOP_UUID_V4, $shopUuid);
        } catch (\Exception $exception) {
            return false;
        }

        return true;
    }
}

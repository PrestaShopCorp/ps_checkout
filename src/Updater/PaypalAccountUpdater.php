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

namespace PrestaShop\Module\PrestashopCheckout\Updater;

use PrestaShop\Module\PrestashopCheckout\Entity\PaypalAccount;
use PrestaShop\Module\PrestashopCheckout\Exception\PsCheckoutException;
use PrestaShop\Module\PrestashopCheckout\PayPal\PayPalMerchantIntegrationProvider;
use PrestaShop\Module\PrestashopCheckout\PersistentConfiguration;

/**
 * Check and set the merchant status
 */
class PaypalAccountUpdater
{
    const SUBSCRIBED = 'SUBSCRIBED';
    const NEED_MORE_DATA = 'NEED_MORE_DATA';
    const IN_REVIEW = 'IN_REVIEW';
    const DENIED = 'DENIED';
    const LIMITED = 'LIMITED';
    const SUSPENDED = 'SUSPENDED';
    const REVOKED = 'REVOKED';

    /* Paypal requires Merchant ID to be 13-characters long at least */
    const MIN_ID_LENGTH = 13;

    /**
     * @var PersistentConfiguration
     */
    private $persistentConfiguration;

    /**
     * @var PayPalMerchantIntegrationProvider
     */
    private $merchantIntegrationProvider;

    public function __construct(PersistentConfiguration $persistentConfiguration, PayPalMerchantIntegrationProvider $merchantIntegrationProvider)
    {
        $this->persistentConfiguration = $persistentConfiguration;
        $this->merchantIntegrationProvider = $merchantIntegrationProvider;
    }

    /**
     * Update the merchant
     *
     * @param PaypalAccount $account
     *
     * @return bool
     *
     * @throws PsCheckoutException
     */
    public function update(PaypalAccount $account)
    {
        $merchantId = $account->getMerchantId();

        if (empty($merchantId)) {
            throw new PsCheckoutException('MerchantId cannot be empty', PsCheckoutException::PSCHECKOUT_MERCHANT_IDENTIFIER_MISSING);
        }

        $merchantIntegration = $this->merchantIntegrationProvider->getById($merchantId);

        if (false === $merchantIntegration) {
            $account->setEmail('');
            $account->setEmailIsVerified('');
            $account->setPaypalPaymentStatus('');
            $account->setCardPaymentStatus('');
            $account->setMerchantCountry('');

            return $this->persistentConfiguration->savePaypalAccount($account);
        }

        $account->setEmail(isset($merchantIntegration['primary_email']) ? $merchantIntegration['primary_email'] : '');
        $account->setEmailIsVerified(isset($merchantIntegration['primary_email_confirmed']) ? $merchantIntegration['primary_email_confirmed'] : '');
        $account->setPaypalPaymentStatus(isset($merchantIntegration['payments_receivable']) ? $merchantIntegration['payments_receivable'] : '');
        $account->setCardPaymentStatus(isset($merchantIntegration['products']) ? $this->getCardStatus($merchantIntegration) : '');
        $account->setMerchantCountry(isset($merchantIntegration['country']) ? $merchantIntegration['country'] : '');

        return $this->persistentConfiguration->savePaypalAccount($account);
    }

    /**
     * Determine the status for hosted fields
     *
     * @param array $response
     *
     * @return string $status status to set in database
     */
    private function getCardStatus($response)
    {
        // PPCP_CUSTOM = product pay by card (hosted fields)
        $cardProductIndex = array_search('PPCP_CUSTOM', array_column($response['products'], 'name'));

        // if product 'PPCP_CUSTOM' doesn't exist disable directly hosted fields
        if (false === $cardProductIndex) {
            return self::DENIED;
        }

        $cardProduct = $response['products'][$cardProductIndex];

        switch ($cardProduct['vetting_status']) {
            case self::SUBSCRIBED:
                $status = $this->cardIsLimited($response);
                break;
            case self::NEED_MORE_DATA:
                $status = self::NEED_MORE_DATA;
                break;
            case self::IN_REVIEW:
                $status = self::IN_REVIEW;
                break;
            default:
                $status = self::DENIED;
                break;
        }

        return $status;
    }

    /**
     * Check if the card is limited in the case where the card is in SUBSCRIBED
     *
     * @param array $response
     *
     * @return string $status
     */
    private function cardIsLimited($response)
    {
        $findCapability = array_search('CUSTOM_CARD_PROCESSING', array_column($response['capabilities'], 'name'));
        $capability = $response['capabilities'][$findCapability];

        // The capability can no longer be used, but there are remediation steps to regain access to the corresponding functionality.
        if ($capability['status'] === 'SUSPENDED') {
            return self::SUSPENDED;
        }

        // The capability can no longer be used and there are no remediation steps available to regain the functionality.
        if ($capability['status'] === 'REVOKED') {
            return self::REVOKED;
        }

        if (isset($capability['limits'])) {
            return self::LIMITED;
        }

        return self::SUBSCRIBED;
    }
}

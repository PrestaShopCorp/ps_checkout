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

namespace PrestaShop\Module\PrestashopCheckout\Dispatcher;

use PrestaShop\Module\PrestashopCheckout\Entity\PaypalAccount;
use PrestaShop\Module\PrestashopCheckout\Exception\PsCheckoutException;
use PrestaShop\Module\PrestashopCheckout\Exception\PsCheckoutSessionException;
use PrestaShop\Module\PrestashopCheckout\PersistentConfiguration;
use PrestaShop\Module\PrestashopCheckout\Updater\PaypalAccountUpdater;

class ShopDispatcher implements Dispatcher
{
    /**
     * @var \Ps_checkout
     */
    private $module;

    public function __construct()
    {
        /** @var \Ps_checkout $module */
        $module = \Module::getInstanceByName('ps_checkout');
        $this->module = $module;
    }

    public function dispatchEventType($payload)
    {
        $this->module->getLogger()->debug(
            'Integrations',
            [
                'shop' => $payload['resource']['shop'],
                'integrations' => isset($payload['resource']['shop']['integrations']) ? $payload['resource']['shop']['integrations'] : null,
            ]
        );
        if (empty($payload['resource']['shop'])) {
            throw new PsCheckoutException('Unable to find shop aggregate', PsCheckoutException::UNKNOWN);
        }

        /** @var \PrestaShop\Module\PrestashopCheckout\Session\Onboarding\OnboardingSessionManager $onboardingSessionManager */
        $onboardingSessionManager = $this->module->getService('ps_checkout.session.onboarding.manager');
        $openedSession = $onboardingSessionManager->getLatestOpenedSession();
        /** @var \PrestaShop\Module\PrestashopCheckout\Session\SessionConfiguration $sessionConfiguration */
        $sessionConfiguration = $this->module->getService('ps_checkout.session.configuration');
        $onboardingSessionConfiguration = $sessionConfiguration->getOnboarding();

        if (!$openedSession) {
            throw new PsCheckoutSessionException('Unable to find an opened onboarding session', PsCheckoutSessionException::OPENED_SESSION_NOT_FOUND);
        }

        $data = json_decode($openedSession->getData());
        $payloadShop = $payload['resource']['shop'];
        $payloadIntegrations = isset($payloadShop['paypal']['integrations']) ? $payloadShop['paypal']['integrations'] : [];
        $data->shop = [
            'paypal_onboarding_url' => $payloadShop['paypal']['onboard']['links'][1]['href'],
            'integrations' => !empty($payloadIntegrations) ? $payloadIntegrations : null,
            'permissions_granted' => isset($payloadIntegrations['has_granted_permissions']) ? $payloadIntegrations['has_granted_permissions'] : null,
            'consent_status' => isset($payloadIntegrations['has_consented_credentials']) ? $payloadIntegrations['has_consented_credentials'] : null,
            'risk_status' => isset($payloadIntegrations['risk_status']) ? $payloadIntegrations['risk_status'] : null,
            'account_status' => isset($payloadIntegrations['account_status']) ? $payloadIntegrations['account_status'] : null,
            'is_email_confirmed' => isset($payloadIntegrations['is_email_confirmed']) ? $payloadIntegrations['is_email_confirmed'] : null,
        ];

        $openedSession->setData(json_encode($data));

        if (!empty($payloadIntegrations)) {
            $paypalAccount = new PaypalAccount(
                isset($payloadIntegrations['merchant_id']) ? $payloadIntegrations['merchant_id'] : null,
                isset($payloadIntegrations['primary_email']) ? $payloadIntegrations['primary_email'] : null,
                isset($payloadIntegrations['primary_email_confirmed']) ? $payloadIntegrations['primary_email_confirmed'] : null,
                isset($payloadIntegrations['payments_receivable']) ? $payloadIntegrations['payments_receivable'] : null,
                $this->getCardStatus($payloadIntegrations),
                isset($payloadIntegrations['country']) ? $payloadIntegrations['country'] : null
            );

            /** @var PersistentConfiguration $persistentConfiguration */
            $persistentConfiguration = $this->module->getService('ps_checkout.persistent.configuration');
            $persistentConfiguration->savePaypalAccount($paypalAccount);
        }

        $this->module->getLogger()->debug(
            'Session and transitions',
            [
                'transitions' => $onboardingSessionConfiguration['transitions'],
                'session' => $openedSession->toArray(),
            ]
        );

        $action = 'onboard_paypal';

        foreach ($onboardingSessionConfiguration['transitions'] as $key => $transition) {
            if (isset($transition['from'])) {
                if ($transition['from'] === $openedSession->getStatus()) {
                    $action = $key;
                } elseif (is_array($transition['from'])) {
                    foreach ($transition['from'] as $trans) {
                        if ($trans === $openedSession->getStatus()) {
                            $action = $key;
                        }
                    }
                }
            }
        }

        $this->module->getLogger()->debug(
            'Action',
            [
                'action' => $action,
            ]
        );

        return (bool) $onboardingSessionManager->apply($action, $openedSession->toArray(true));
    }

    /**
     * Determine the status for hosted fields
     *
     * @todo Remove this
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
            return PaypalAccountUpdater::DENIED;
        }

        $cardProduct = $response['products'][$cardProductIndex];

        switch ($cardProduct['vetting_status']) {
            case PaypalAccountUpdater::SUBSCRIBED:
                $status = $this->cardIsLimited($response);
                break;
            case PaypalAccountUpdater::NEED_MORE_DATA:
                $status = PaypalAccountUpdater::NEED_MORE_DATA;
                break;
            case PaypalAccountUpdater::IN_REVIEW:
                $status = PaypalAccountUpdater::IN_REVIEW;
                break;
            default:
                $status = PaypalAccountUpdater::DENIED;
                break;
        }

        return $status;
    }

    /**
     * Check if the card is limited in the case where the card is in SUBSCRIBED
     *
     * @todo Remove this
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
            return PaypalAccountUpdater::SUSPENDED;
        }

        // The capability can no longer be used and there are no remediation steps available to regain the functionality.
        if ($capability['status'] === 'REVOKED') {
            return PaypalAccountUpdater::REVOKED;
        }

        if (isset($capability['limits'])) {
            return PaypalAccountUpdater::LIMITED;
        }

        return PaypalAccountUpdater::SUBSCRIBED;
    }
}

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

namespace PrestaShop\Module\PrestashopCheckout\Api\Payment;

use PrestaShop\Module\PrestashopCheckout\Api\Payment\Client\PaymentClient;

/**
 * Handle Webhook requests
 */
class Webhook extends PaymentClient
{
    const CATEGORY = [
        'SHOP' => 'SHOP'
    ];

    /**
     * Tells if the webhook came from the PSL
     *
     * @param array $payload
     *
     * @return array
     */
    public function getShopSignature(array $payload)
    {
        if ($payload['category'] === self::CATEGORY['SHOP']) {
            /** @var \PrestaShop\Module\PrestashopCheckout\Session\Onboarding\OnboardingSessionManager */
            $onboardingSessionManager = $this->module->getService('ps_checkout.session.onboarding.manager');
            $openedOnboardingSession = $onboardingSessionManager->getLatestOpenedSession();

            $this->setRoute("/webhooks/${payload['id']}/verify");
            return $this->get([
                'headers' => [
                    'X-Correlation-Id' => $openedOnboardingSession->getCorrelationId(),
                    'Session-Token' => $openedOnboardingSession->getAuthToken(),
                ],
                'json' => $payload,
            ]);
        } else {
            $this->setRoute('/payments/shop/verify_webhook_signature');
            return $this->post([
                'json' => $payload,
            ]);
        }
    }
}

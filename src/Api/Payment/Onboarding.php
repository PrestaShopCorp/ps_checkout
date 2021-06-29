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
use PrestaShop\Module\PrestashopCheckout\Builder\Payload\OnboardingPayloadBuilder;

/**
 * Handle onbarding request
 */
class Onboarding extends PaymentClient
{
    /**
     * Generate the paypal link to onboard merchant
     *
     * @return array response (ResponseApiHandler class)
     */
    public function getOnboardingLink()
    {
        $this->setRoute('/payments/onboarding/onboard');
        /** @var \Ps_checkout $module */
        $module = \Module::getInstanceByName('ps_checkout');
        /** @var OnboardingPayloadBuilder $builder */
        $builder = $module->getService('ps_checkout.builder.payload.onboarding');
        /** @var \PrestaShop\Module\PrestashopCheckout\Session\Onboarding\OnboardingSessionManager */
        $onboardingSessionManager = $module->getService('ps_checkout.session.onboarding.manager');
        $openedOnboardingSession = $onboardingSessionManager->getOpened();

        $builder->buildFullPayload();

        $response = $this->post([
            'headers' => [
                'X-Correlation-Id' => $openedOnboardingSession->getCorrelationId(),
                'Session-Token' => $openedOnboardingSession->getAuthToken(),
            ],
            'json' => $builder->presentPayload()->getJson(),
        ]);

        if (false === isset($response['body']['links']['1']['href']) || '200' !== (string) $response['httpCode']) {
            $response['status'] = false;

            return $response;
        }

        $response['onboardingLink'] = $response['body']['links']['1']['href'];

        return $response;
    }
}

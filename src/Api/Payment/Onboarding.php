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
use PrestaShop\Module\PrestashopCheckout\ShopContext;

/**
 * Handle onbarding request
 */
class Onboarding extends PaymentClient
{
    /**
     * Create shop on PSL
     *
     * @param array $data
     *
     * @return array (ResponseApiHandler class)
     */
    public function createShop(array $data)
    {
        /** @var \PrestaShop\Module\PrestashopCheckout\Session\Onboarding\OnboardingSessionManager */
        $onboardingSessionManager = $this->module->getService('ps_checkout.session.onboarding.manager');
        $openedOnboardingSession = $onboardingSessionManager->getOpened();

        $this->setRoute('/shops');

        return $this->post([
            'headers' => [
                'X-Correlation-Id' => $openedOnboardingSession->getCorrelationId(),
                'Session-Token' => $openedOnboardingSession->getAuthToken(),
            ],
            'json' => $data,
        ]);
    }

    /**
     * Onboard a merchant on PSL
     *
     * @return array (ResponseApiHandler class)
     */
    public function onboard()
    {
        $this->setRoute('/payments/onboarding/onboard');
        /** @var OnboardingPayloadBuilder $builder */
        $builder = $this->module->getService('ps_checkout.builder.payload.onboarding');
        /** @var ShopContext $shopContext */
        $shopContext = $this->module->getService('ps_checkout.context.shop');
        /** @var \PrestaShop\Module\PrestashopCheckout\Session\Onboarding\OnboardingSessionManager */
        $onboardingSessionManager = $this->module->getService('ps_checkout.session.onboarding.manager');
        $openedOnboardingSession = $onboardingSessionManager->getOpened();

        $builder->buildFullPayload();

        if ($shopContext->isReady()) {
            $builder->buildMinimalPayload();
        }

        $response = $this->post([
            'headers' => [
                'X-Correlation-Id' => $openedOnboardingSession->getCorrelationId(),
                'Session-Token' => $openedOnboardingSession->getAuthToken(),
            ],
            'json' => $builder->presentPayload()->getArray(),
        ]);

        // Retry with minimal payload when full payload failed
        if (substr((string) $response['httpCode'], 0, 1) === '4') {
            $builder->buildMinimalPayload();
            $response = $this->post([
                'headers' => [
                    'X-Correlation-Id' => $openedOnboardingSession->getCorrelationId(),
                    'Session-Token' => $openedOnboardingSession->getAuthToken(),
                ],
                'json' => $builder->presentPayload()->getArray(),
            ]);
        }

        if (false === $response['status']) {
            return $response;
        }

        if (false === isset($response['body']['links']['1']['href'])) {
            $response['status'] = false;

            return $response;
        }

        $response['onboardingLink'] = $response['body']['links']['1']['href'];

        return $response;
    }
}

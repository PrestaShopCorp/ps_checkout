<?php
/**
 * 2007-2020 PrestaShop and Contributors
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2020 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaShop SA
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
     * Generate the paypal link to onboard merchant
     *
     * @return array response (ResponsaApiHandler class)
     */
    public function getOnboardingLink()
    {
        $this->setRoute('/payments/onboarding/onboard');

        $builder = new OnboardingPayloadBuilder();

        $builder->buildFullPayload();

        if ((new ShopContext())->isReady()) {
            $builder->buildMinimalPayload();
        }

        $response = $this->post([
            'json' => $builder->presentPayload()->getJson(),
        ]);

        // Retry with minimal payload when full payload failed
        if (substr((string) $response['httpCode'], 0, 1) === '4') {
            $builder->buildMinimalPayload();
            $response = $this->post([
                'json' => $builder->presentPayload()->getJson(),
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

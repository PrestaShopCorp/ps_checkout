<?php
/**
 * 2007-2019 PrestaShop and Contributors
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
 * @copyright 2007-2019 PrestaShop SA and Contributors
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
     * @return array|string onboarding link
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

        if ($response['httpCode'] === 400) {
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

        return $response['body']['links']['1']['href'];
    }
}

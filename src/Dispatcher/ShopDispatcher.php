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

use PrestaShop\Module\PrestashopCheckout\Exception\PsCheckoutException;
use PrestaShop\Module\PrestashopCheckout\Exception\PsCheckoutSessionException;

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
        if (empty($payload['resource']['shop'])) {
            throw new PsCheckoutException('Unable to found shop aggregate', PsCheckoutException::UNKNOWN);
        }

        /** @var \PrestaShop\Module\PrestashopCheckout\Session\Onboarding\OnboardingSessionManager $onboardingSessionManager */
        $onboardingSessionManager = $this->module->getService('ps_checkout.session.onboarding.manager');
        $openedSession = $onboardingSessionManager->getLatestOpenedSession();

        if (!$openedSession) {
            throw new PsCheckoutSessionException('Unable to find an opened onboarding session', PsCheckoutSessionException::OPENED_SESSION_NOT_FOUND);
        }

        $data = json_decode($openedSession->getData(), true);
        $data['shop'] = $payload['resource']['shop'];

        $openedSession->setData(json_encode($data));

        return (bool) $onboardingSessionManager->apply('create_shop', $openedSession->toArray(true));
    }
}

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

namespace PrestaShop\Module\PrestashopCheckout\PayPal;

use PrestaShop\Module\PrestashopCheckout\Session\Onboarding\OnboardingSessionManager;
use Psr\SimpleCache\CacheInterface;

class PayPalMerchantIntegrationProvider
{
    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @var OnboardingSessionManager
     */
    private $onboardingSessionManager;

    /**
     * @param CacheInterface $cache
     * @param OnboardingSessionManager $onboardingSessionManager
     */
    public function __construct(
        CacheInterface $cache,
        OnboardingSessionManager $onboardingSessionManager
    ) {
        $this->cache = $cache;
        $this->onboardingSessionManager = $onboardingSessionManager;
    }

    /**
     * @param string $id PayPal Merchant Id
     *
     * @return array|false
     */
    public function getById($id)
    {
        if ($this->cache->has($id)) {
            return $this->cache->get($id);
        }

        $openedOnboardingSession = $this->onboardingSessionManager->getOpened();
        $sessionData = $openedOnboardingSession ?
            json_decode($openedOnboardingSession->getData()) :
            null;
        $shopData = $sessionData && isset($sessionData->shop) ?
            $sessionData->shop :
            null;
        $merchantIntegrations = $shopData && isset($shopData->integrations) ?
            (array) $shopData->integrations :
            null;

        if (!$merchantIntegrations) {
            return false;
        }

        $this->cache->set($id, $merchantIntegrations);

        return $merchantIntegrations;
    }
}

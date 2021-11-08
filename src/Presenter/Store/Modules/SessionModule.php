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

namespace PrestaShop\Module\PrestashopCheckout\Presenter\Store\Modules;

use PrestaShop\Module\PrestashopCheckout\OnBoarding\OnboardingStateHandler;
use PrestaShop\Module\PrestashopCheckout\Presenter\PresenterInterface;
use Psr\SimpleCache\CacheInterface;

/**
 * Construct the session module
 */
class SessionModule implements PresenterInterface
{
    /**
     * @var OnboardingStateHandler
     */
    private $onboardingStateHandler;

    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @param OnboardingStateHandler $onboardingStateHandler
     * @param CacheInterface $cache
     */
    public function __construct(
        OnboardingStateHandler $onboardingStateHandler,
        CacheInterface $cache
    ) {
        $this->onboardingStateHandler = $onboardingStateHandler;
        $this->cache = $cache;
    }

    /**
     * Present the session module (vuex)
     *
     * @return array
     */
    public function present()
    {
        return [
            'session' => [
                'onboarding' => $this->onboardingStateHandler->handle(),
                'error' => $this->cache->get('session-error'),
            ],
        ];
    }
}

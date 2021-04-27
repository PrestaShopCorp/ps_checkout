<?php

/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 */

namespace PrestaShop\Module\PrestashopCheckout\Session\Onboarding;

class OnboardingStatus
{
    const ONBOARDING_STARTED = 'ONBOARDING_STARTED';
    const ACCOUNT_ONBOARDING_STARTED = 'ACCOUNT_ONBOARDING_STARTED';
    const FIREBASE_ONBOARDED = 'FIREBASE_ONBOARDED';
    const ACCOUNT_ONBOARDED = 'ACCOUNT_ONBOARDED';
    const PAYPAL_ONBOARDING_STARTED = 'PAYPAL_ONBOARDING_STARTED';
    const ONBOARDING_FINISHED = 'ONBOARDING_FINISHED';
}

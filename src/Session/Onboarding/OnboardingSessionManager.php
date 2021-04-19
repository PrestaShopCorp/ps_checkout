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

use PrestaShop\Module\PrestashopCheckout\Session\Session;
use PrestaShop\Module\PrestashopCheckout\Session\SessionHelper;
use PrestaShop\Module\PrestashopCheckout\Session\SessionManager;
use PrestaShop\Module\PrestashopCheckout\Session\SessionRepository;
use PrestaShop\Module\PrestashopCheckout\Session\Onboarding\OnboardingSatus;

class OnboardingSessionManager extends SessionManager
{
    /**
     * @var \Context
     */
    private $context;

    /**
     * @var PrestaShop\Module\PrestashopCheckout\Session\Onboarding\OnboardingStatus
     */
    private $onboardingStatus;

    public function __construct(SessionRepository $sessionRepository, OnboardingStatus $onboardingStatus)
    {
        parent::__construct($sessionRepository);
        $this->context = \Context::getContext();
        $this->onboardingStatus = $onboardingStatus;
    }

    /**
     * Start a merchant onboarding session
     *
     * @param array $sessionData
     *
     * @return PrestaShop\Module\PrestashopCheckout\Session\Session
     */
    public function startOnboarding()
    {
        $sessionData = [
            'user_id' => (int) $this->context->employee->id,
            'shop_id' => (int) $this->context->shop->id,
            'process_type' => 'onboarding',
            'status' => OnboardingStatus::ONBOARDING_STARTED,
        ];

        $sessionData = $this->onboardingStatus->defineStartingStatus($sessionData);

        return $this->start($sessionData);
    }

    /**
     * Update a merchant onboarding session status to FIREBASE_ONBOARDING_STARTED
     *
     * @param PrestaShop\Module\PrestashopCheckout\Session\Session $session
     *
     * @return PrestaShop\Module\PrestashopCheckout\Session\Session
     */
    public function toFirebaseOnboardingStarted(Session $session)
    {
        if ($session->getStatus() !== OnboardingStatus::ONBOARDING_STARTED) {
            return $session;
        }

        $session->setStatus(OnboardingStatus::FIREBASE_ONBOARDING_STARTED);
        $session->setExpirationDate(SessionHelper::updateExpirationDate(date('Y-m-d H:i:s')));
        $this->update($session);

        return $this->get($session->toArray());
    }

    /**
     * Update a merchant onboarding session status to FIREBASE_ONBOARDED
     *
     * @param PrestaShop\Module\PrestashopCheckout\Session\Session $session
     *
     * @return PrestaShop\Module\PrestashopCheckout\Session\Session
     */
    public function toFirebaseOnboarded(Session $session)
    {
        if ($session->getStatus() !== OnboardingStatus::FIREBASE_ONBOARDING_STARTED) {
            return $session;
        }

        $session->setStatus(OnboardingStatus::FIREBASE_ONBOARDED);
        $session->setExpirationDate(null);
        $this->update($session);

        return $this->get($session->toArray());
    }

    /**
     * Update a merchant onboarding session status to ACCOUNT_ONBOARDING_STARTED
     *
     * @param PrestaShop\Module\PrestashopCheckout\Session\Session $session
     *
     * @return PrestaShop\Module\PrestashopCheckout\Session\Session
     */
    public function toAccountOnboardingStarted(Session $session)
    {
        if (
            $session->getStatus() !== OnboardingStatus::FIREBASE_ONBOARDED &&
            $session->getStatus() !== OnboardingStatus::ONBOARDING_STARTED
        ) {
            return $session;
        }

        $session->setStatus(OnboardingStatus::ACCOUNT_ONBOARDING_STARTED);
        $session->setExpirationDate(SessionHelper::updateExpirationDate(date('Y-m-d H:i:s')));
        $this->update($session);

        return $this->get($session->toArray());
    }

    /**
     * Update a merchant onboarding session status to ACCOUNT_ONBOARDED
     *
     * @param PrestaShop\Module\PrestashopCheckout\Session\Session $session
     *
     * @return PrestaShop\Module\PrestashopCheckout\Session\Session
     */
    public function toAccountOnboarded(Session $session)
    {
        if ($session->getStatus() !== OnboardingStatus::ACCOUNT_ONBOARDING_STARTED) {
            return $session;
        }

        $session->setStatus(OnboardingStatus::ACCOUNT_ONBOARDED);
        $session->setExpirationDate(null);
        $this->update($session);

        return $this->get($session->toArray());
    }

    /**
     * Update a merchant onboarding session status to PAYPAL_ONBOARDING_STARTED
     *
     * @param PrestaShop\Module\PrestashopCheckout\Session\Session $session
     *
     * @return PrestaShop\Module\PrestashopCheckout\Session\Session
     */
    public function toPaypalOnboardingStarted(Session $session)
    {
        if ($session->getStatus() !== OnboardingStatus::ACCOUNT_ONBOARDED) {
            return $session;
        }

        $session->setStatus(OnboardingStatus::PAYPAL_ONBOARDING_STARTED);
        $session->setExpirationDate(SessionHelper::updateExpirationDate(date('Y-m-d H:i:s')));
        $this->update($session);

        return $this->get($session->toArray());
    }

    /**
     * Update a merchant onboarding session status to ONBOARDING_FINISHED
     *
     * @param PrestaShop\Module\PrestashopCheckout\Session\Session $session
     *
     * @return PrestaShop\Module\PrestashopCheckout\Session\Session
     */
    public function toOnboardingFinished(Session $session)
    {
        if ($session->getStatus() !== OnboardingStatus::PAYPAL_ONBOARDING_STARTED) {
            return $session;
        }

        $session->setStatus(OnboardingStatus::ONBOARDING_FINISHED);
        $session->setExpirationDate(null);
        $this->update($session);

        return $this->get($session->toArray());
    }

    /**
     * Restart a merchant onboarding session
     *
     * @param PrestaShop\Module\PrestashopCheckout\Session\Session $session
     *
     * @return PrestaShop\Module\PrestashopCheckout\Session\Session
     */
    public function restartOnboarding(Session $session)
    {
        $this->stop($session);

        return $this->startOnboarding();
    }
}

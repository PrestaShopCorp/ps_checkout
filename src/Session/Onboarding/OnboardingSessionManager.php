<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */

namespace PrestaShop\Module\PrestashopCheckout\Session\Onboarding;

use PrestaShop\Module\PrestashopCheckout\Api\Payment\Authentication;
use PrestaShop\Module\PrestashopCheckout\Exception\PsCheckoutSessionException;
use PrestaShop\Module\PrestashopCheckout\Session\Session;
use PrestaShop\Module\PrestashopCheckout\Session\SessionConfiguration;
use PrestaShop\Module\PrestashopCheckout\Session\SessionHelper;
use PrestaShop\Module\PrestashopCheckout\Session\SessionManager;
use Ramsey\Uuid\Uuid;

class OnboardingSessionManager extends SessionManager
{
    /**
     * @var \Context
     */
    private $context;

    /**
     * @var array
     */
    private $configuration;

    /**
     * @var array
     */
    private $states;

    /**
     * @var array
     */
    private $transitions;

    /**
     * @param \PrestaShop\Module\PrestashopCheckout\Session\Onboarding\OnboardingSessionRepository $repository
     * @param \PrestaShop\Module\PrestashopCheckout\Session\SessionConfiguration $configuration
     *
     * @return void
     */
    public function __construct(OnboardingSessionRepository $repository, SessionConfiguration $configuration)
    {
        parent::__construct($repository);
        $this->context = \Context::getContext();
        $this->configuration = $configuration->getOnboarding();
        $this->states = $this->configuration['states'];
        $this->transitions = $this->configuration['transitions'];
    }

    /**
     * Open a merchant onboarding session
     *
     * @param object $data
     * @param bool $isShopCreated Start an onboarding session with SHOP_CREATED status
     *
     * @return \PrestaShop\Module\PrestashopCheckout\Session\Session
     */
    public function openOnboarding($data, $isShopCreated = false)
    {
        $paymentAuthentication = new Authentication(\Context::getContext()->link);
        $authToken = $paymentAuthentication->getAuthToken();
        $authToken = [
            'auth_token' => Uuid::uuid4()->toString(),
            'expires_at' => SessionHelper::updateExpirationDate(date('Y-m-d H:i:s')),
        ];
        $sessionData = [
            'user_id' => (int) $this->context->employee->id,
            'shop_id' => (int) $this->context->shop->id,
            'is_closed' => false,
            'auth_token' => $authToken['auth_token'],
            'status' => $isShopCreated ? $this->states['SHOP_CREATED'] : $this->configuration['initial_state'],
            'expires_at' => $authToken['expires_at'],
            'is_sse_opened' => false,
            'data' => json_encode($data),
        ];

        return $this->open($sessionData);
    }

    /**
     * Get an opened merchant onboarding session
     *
     * @return \PrestaShop\Module\PrestashopCheckout\Session\Session|null
     */
    public function getOpened()
    {
        $sessionData = [
            'user_id' => (int) $this->context->employee->id,
            'shop_id' => (int) $this->context->shop->id,
            'is_closed' => false,
        ];

        return $this->get($sessionData);
    }

    /**
     * Check if an onboarding session transition is authorized from a state to another
     *
     * @param string $next Next state to transit
     * @param array $update Session data to update
     *
     * @return void
     *
     * @throws \PrestaShop\Module\PrestashopCheckout\Exception\PsCheckoutSessionException
     */
    public function can($next, array $update)
    {
        $nextTransition = $this->transitions[$next];
        $updateConfiguration = $nextTransition['update'];
        $updateIntersect = SessionHelper::recursiveArrayIntersectKey($update, $updateConfiguration);
        $sortedUpdateConfiguration = SessionHelper::sortMultidimensionalArray($updateConfiguration);
        $genericErrorMsg = 'Unable to transit this session : ';

        if (!$nextTransition) {
            throw new PsCheckoutSessionException($genericErrorMsg . 'Unexisting session transition', PsCheckoutSessionException::UNEXISTING_SESSION_TRANSITION);
        }

        if (!$this->getOpened()) {
            throw new PsCheckoutSessionException($genericErrorMsg . 'Unable to find an opened session', PsCheckoutSessionException::OPENED_SESSION_NOT_FOUND);
        }

        if ($this->getOpened()->getStatus() !== $nextTransition['from']) {
            throw new PsCheckoutSessionException($genericErrorMsg . 'The session is not authorized to transit from ' . $this->getOpened()->getStatus() . ' to ' . $nextTransition['to'], PsCheckoutSessionException::FORBIDDEN_SESSION_TRANSITION);
        }

        if ($updateIntersect !== $sortedUpdateConfiguration) {
            throw new PsCheckoutSessionException($genericErrorMsg . 'Missing expected update session parameters.', PsCheckoutSessionException::MISSING_EXPECTED_PARAMETERS);
        }
    }

    /**
     * Apply the onboarding session transition
     *
     * @param string $next Next state to transit
     * @param array $update Session data to update
     *
     * @return \PrestaShop\Module\PrestashopCheckout\Session\Session
     *
     * @throws \Exception
     */
    public function apply($next, array $update)
    {
        $this->can($next, $update);

        $nextTransition = $this->transitions[$next];
        $session = $this->getOpened();

        foreach ($update as $updateKey => $updateValue) {
            foreach ($nextTransition['update'] as $updateConfigKey => $updateConfigValue) {
                if ($updateKey === $updateConfigKey) {
                    if ($updateKey === 'data') {
                        $value = json_encode($updateValue);
                    } elseif ($updateConfigValue !== null) {
                        $value = $updateConfigValue;
                    } else {
                        $value = $updateValue;
                    }

                    $set = 'set' . SessionHelper::snakeToPascalCase($updateKey);

                    $session->$set($value);
                }
            }
        }

        $session->setStatus($nextTransition['to']);
        $this->update($session);

        return $this->get($session->toArray());
    }

    /**
     * Close a merchant onboarding session
     *
     * @param \PrestaShop\Module\PrestashopCheckout\Session\Session $session
     *
     * @return bool
     */
    public function closeOnboarding(Session $session)
    {
        return $this->close($session);
    }

    /**
     * Get latest opened onboarding session for webhooks
     *
     * @return \PrestaShop\Module\PrestashopCheckout\Session\Session|null
     */
    public function getLatestOpenedSession()
    {
        $sessionData = [
            'shop_id' => (int) $this->context->shop->id,
            'is_closed' => false,
        ];

        return $this->get($sessionData);
    }
}

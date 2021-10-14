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

namespace PrestaShop\Module\PrestashopCheckout\Session\Onboarding;

use PrestaShop\Module\PrestashopCheckout\Api\Psl\Authentication;
use PrestaShop\Module\PrestashopCheckout\Api\Psl\Onboarding;
use PrestaShop\Module\PrestashopCheckout\Configuration\PrestaShopConfiguration;
use PrestaShop\Module\PrestashopCheckout\Context\PrestaShopContext;
use PrestaShop\Module\PrestashopCheckout\Exception\PsCheckoutSessionException;
use PrestaShop\Module\PrestashopCheckout\PayPal\Mode;
use PrestaShop\Module\PrestashopCheckout\PayPal\PayPalConfiguration;
use PrestaShop\Module\PrestashopCheckout\Session\Session;
use PrestaShop\Module\PrestashopCheckout\Session\SessionConfiguration;
use PrestaShop\Module\PrestashopCheckout\Session\SessionHelper;
use PrestaShop\Module\PrestashopCheckout\Session\SessionManager;
use Ramsey\Uuid\Uuid;

class OnboardingSessionManager extends SessionManager
{
    // TODO export to outside enum
    const SHOP_SESSION = 'shop';

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
     * @var string
     */
    private $mode;

    /**
     * @param OnboardingSessionRepository $repository
     * @param SessionConfiguration $configuration
     * @param PrestaShopConfiguration $prestashopConfiguration
     *
     * @return void
     */
    public function __construct(
        OnboardingSessionRepository $repository,
        SessionConfiguration $configuration,
        PrestaShopConfiguration $prestashopConfiguration
    ) {
        parent::__construct($repository);
        $this->context = \Context::getContext();
        $this->configuration = $configuration->getOnboarding();
        $this->states = $this->configuration['states'];
        $this->transitions = $this->configuration['transitions'];
        $this->mode = Mode::LIVE === $prestashopConfiguration->get(PayPalConfiguration::PAYMENT_MODE) ? Mode::LIVE : Mode::SANDBOX;
    }

    /**
     * Open a merchant onboarding session
     *
     * @param object $data
     *
     * @return Session
     *
     * @throws PsCheckoutSessionException
     */
    public function openOnboarding($data)
    {
        $correlationId = Uuid::uuid4()->toString();


        // Shop UUID generation from PSL
        $onboardingApi = new Onboarding(new PrestaShopContext());

        $onboardingApi->createShopUuid($correlationId);

        $authenticationApi = new Authentication(new PrestaShopContext());
        $authToken = $authenticationApi->getAuthToken(self::SHOP_SESSION, $correlationId);
        $createdAt = date('Y-m-d H:i:s');
        $sessionData = [
            'correlation_id' => $correlationId,
            'mode' => $this->mode,
            'user_id' => (int) $this->context->employee->id,
            'shop_id' => (int) $this->context->shop->id,
            'is_closed' => false,
            'auth_token' => $authToken['token'],
            'status' => $this->configuration['initial_state'],
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
            'expires_at' => $authToken['expires_at'],
            'is_sse_opened' => false,
            'data' => (array) $data,
        ];

        $this->can('start', $sessionData);

        // Shop UUID generation from PSL
        $onboardingApi = new Onboarding(new PrestaShopContext());

        $onboardingApi->createShopUuid();

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
            'mode' => $this->mode,
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
        $action = $next === 'start' ? 'open' : 'transit';
        $genericErrorMsg = 'Unable to ' . $action . ' this session : ';
        /** @var \Ps_checkout $module */
        $module = \Module::getInstanceByName('ps_checkout');

        if (!$nextTransition) {
            $exception = new PsCheckoutSessionException($genericErrorMsg . 'Unexisting session transition', PsCheckoutSessionException::UNEXISTING_SESSION_TRANSITION);
            $module->getLogger()->error('Unexisting session transition', ['exception' => $exception, 'trace' => $exception->getTraceAsString()]);
            throw $exception;
        }

        // Exceptions only for transit actions
        if ($action === 'transit') {
            if (!$this->getCurrentSession()) {
                $exception = new PsCheckoutSessionException($genericErrorMsg . 'Unable to find an opened session', PsCheckoutSessionException::OPENED_SESSION_NOT_FOUND);
                $module->getLogger()->error('Unable to find an opened session', ['exception' => $exception, 'trace' => $exception->getTraceAsString()]);
                throw $exception;
            }

            $authorizedTransition = false;

            if (is_array($nextTransition['from'])) {
                foreach ($nextTransition['from'] as $transition) {
                    if ($this->getCurrentSession()->getStatus() === $transition) {
                        $authorizedTransition = true;
                        break;
                    }
                }
            }
            if ($this->getCurrentSession()->getStatus() === $nextTransition['from']) {
                $authorizedTransition = true;
            }

            if (!$authorizedTransition) {
                $exception = new PsCheckoutSessionException($genericErrorMsg . 'The session is not authorized to transit from ' . $this->getCurrentSession()->getStatus() . ' to ' . $nextTransition['to'], PsCheckoutSessionException::FORBIDDEN_SESSION_TRANSITION);
                $module->getLogger()->error('The session transition is not authorized', ['from' => $this->getCurrentSession()->getStatus(), 'to' => $nextTransition['to'], 'exception' => $exception, 'trace' => $exception->getTraceAsString()]);
                throw $exception;
            }
        }

        if ($updateIntersect !== $sortedUpdateConfiguration) {
            $exception = new PsCheckoutSessionException($genericErrorMsg . 'Missing expected update session parameters.' . PHP_EOL . 'Transition : ' . $next, PsCheckoutSessionException::MISSING_EXPECTED_PARAMETERS);
            $module->getLogger()->error($exception->getMessage(), ['transition' => $nextTransition, 'update' => $update, 'updateIntersect' => $updateIntersect, 'sortedUpdateConfiguration' => $sortedUpdateConfiguration, 'exception' => $exception, 'trace' => $exception->getTraceAsString()]);
            throw $exception;
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
     * @throws \PrestaShop\Module\PrestashopCheckout\Exception\PsCheckoutSessionException
     */
    public function apply($next, array $update)
    {
        $this->can($next, $update);

        $nextTransition = $this->transitions[$next];
        $session = $this->getCurrentSession();

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
            'mode' => $this->mode,
            'shop_id' => (int) $this->context->shop->id,
            'is_closed' => false,
        ];

        return $this->get($sessionData);
    }

    /**
     * Get the opened session according to PrestaShop context
     *
     * @return \PrestaShop\Module\PrestashopCheckout\Session\Session|null
     */
    public function getCurrentSession()
    {
        return \Validate::isLoadedObject($this->context->employee) ?
            $this->getOpened() :
            $this->getLatestOpenedSession();
    }
}

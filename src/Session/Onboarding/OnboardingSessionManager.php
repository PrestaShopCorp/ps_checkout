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
use Ps_checkout;
use Psr\SimpleCache\CacheInterface;
use Ramsey\Uuid\Uuid;

class OnboardingSessionManager extends SessionManager
{
    // TODO export to outside enum
    const SHOP_SESSION = 'shop';

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
     * @var Ps_checkout
     */
    private $module;

    /**
     * @var CacheInterface
     */
    private $cache;
    /**
     * @var PrestaShopContext
     */
    private $prestaShopContext;
    /**
     * @var Onboarding
     */
    private $onboardingApi;
    /**
     * @var Authentication
     */
    private $authenticationApi;

    /**
     * @param OnboardingSessionRepository $repository
     * @param SessionConfiguration $configuration
     * @param PrestaShopConfiguration $prestashopConfiguration
     * @param CacheInterface $cache
     *
     * @return void
     */
    public function __construct(
        OnboardingSessionRepository $repository,
        SessionConfiguration $configuration,
        PrestaShopConfiguration $prestashopConfiguration,
        CacheInterface $cache,
        PrestaShopContext $prestaShopContext,
        Ps_checkout $module,
        Onboarding $onboardingApi,
        Authentication $authenticationApi
    ) {
        parent::__construct($repository);
        $this->configuration = $configuration->getOnboarding();
        $this->states = $this->configuration['states'];
        $this->transitions = $this->configuration['transitions'];
        $this->mode = Mode::LIVE === $prestashopConfiguration->get(PayPalConfiguration::PAYMENT_MODE) ? Mode::LIVE : Mode::SANDBOX;
        $this->cache = $cache;
        $this->prestaShopContext = $prestaShopContext;
        $this->module = $module;
        $this->onboardingApi = $onboardingApi;
        $this->authenticationApi = $authenticationApi;
    }

    /**
     * Open a onboarding session
     *
     * @param object $data
     *
     * @return Session|null
     *
     * @throws PsCheckoutSessionException
     */
    public function openOnboarding($data)
    {
        $correlationId = Uuid::uuid4()->toString();

        // Shop UUID generation from PSL
        $createShopUuid = $this->onboardingApi->createShopUuid($correlationId);

        if (!$createShopUuid) {
            return null;
        }

        $authToken = $this->authenticationApi->getAuthToken(self::SHOP_SESSION, $correlationId);

        if (!$authToken) {
            return null;
        }

        $createdAt = date('Y-m-d H:i:s');
        $sessionData = [
            'correlation_id' => $correlationId,
            'mode' => $this->mode,
            'shop_id' => $this->prestaShopContext->getShopId(),
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

        return $this->open($sessionData);
    }

    /**
     * Get the opened onboarding session
     *
     * @return \PrestaShop\Module\PrestashopCheckout\Session\Session|null
     */
    public function getOpened()
    {
        $sessionData = [
            'mode' => $this->mode,
            'shop_id' => $this->prestaShopContext->getShopId(),
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

        if (!$nextTransition) {
            $exception = new PsCheckoutSessionException($genericErrorMsg . 'Unexisting session transition', PsCheckoutSessionException::UNEXISTING_SESSION_TRANSITION);
            $this->module->getLogger()->error('Unexisting session transition', ['exception' => $exception, 'trace' => $exception->getTraceAsString()]);
            throw $exception;
        }

        // Exceptions only for transit actions
        if ($action === 'transit') {
            if (!$this->getOpened()) {
                $exception = new PsCheckoutSessionException($genericErrorMsg . 'Unable to find an opened session', PsCheckoutSessionException::OPENED_SESSION_NOT_FOUND);
                $this->module->getLogger()->error('Unable to find an opened session', ['exception' => $exception, 'trace' => $exception->getTraceAsString()]);
                throw $exception;
            }

            $authorizedTransition = false;

            if (is_array($nextTransition['from'])) {
                foreach ($nextTransition['from'] as $transition) {
                    if ($this->getOpened()->getStatus() === $transition) {
                        $authorizedTransition = true;
                        break;
                    }
                }
            }

            if ($this->getOpened()->getStatus() === $nextTransition['from']) {
                $authorizedTransition = true;
            }

            if (!$authorizedTransition) {
                $exception = new PsCheckoutSessionException($genericErrorMsg . 'The session is not authorized to transit from ' . $this->getOpened()->getStatus() . ' to ' . $nextTransition['to'], PsCheckoutSessionException::FORBIDDEN_SESSION_TRANSITION);
                $this->module->getLogger()->error('The session transition is not authorized', ['from' => $this->getOpened()->getStatus(), 'to' => $nextTransition['to'], 'exception' => $exception, 'trace' => $exception->getTraceAsString()]);
                throw $exception;
            }
        }

        if ($updateIntersect !== $sortedUpdateConfiguration) {
            $exception = new PsCheckoutSessionException($genericErrorMsg . 'Missing expected update session parameters.' . PHP_EOL . 'Transition : ' . $next, PsCheckoutSessionException::MISSING_EXPECTED_PARAMETERS);
            $this->module->getLogger()->error($exception->getMessage(), ['transition' => $nextTransition, 'update' => $update, 'updateIntersect' => $updateIntersect, 'sortedUpdateConfiguration' => $sortedUpdateConfiguration, 'exception' => $exception, 'trace' => $exception->getTraceAsString()]);
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
}

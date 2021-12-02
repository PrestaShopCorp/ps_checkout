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

namespace PrestaShop\Module\PrestashopCheckout\Api\Psl;

use GuzzleHttp\Client;
use PrestaShop\Module\PrestashopCheckout\Adapter\LinkAdapter;
use PrestaShop\Module\PrestashopCheckout\Api\Firebase\Token;
use PrestaShop\Module\PrestashopCheckout\Api\Psl\Client\PslClient;
use PrestaShop\Module\PrestashopCheckout\Builder\Payload\OnboardingPayloadBuilder;
use PrestaShop\Module\PrestashopCheckout\Configuration\PrestaShopConfiguration;
use PrestaShop\Module\PrestashopCheckout\Context\PrestaShopContext;
use PrestaShop\Module\PrestashopCheckout\Entity\PsAccount;
use PrestaShop\Module\PrestashopCheckout\Handler\ExceptionHandler;
use PrestaShop\Module\PrestashopCheckout\Session\Session;
use PrestaShop\Module\PrestashopCheckout\ShopContext;
use PrestaShop\Module\PrestashopCheckout\ShopUuidManager;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;

/**
 * Handle onbarding request
 */
class Onboarding extends PslClient
{
    /**
     * @var OnboardingPayloadBuilder
     */
    private $onboardingPayloadBuilder;
    /**
     * @var ShopContext
     */
    private $shopContext;

    public function __construct(
        ExceptionHandler $exceptionHandler,
        LoggerInterface $logger,
        PrestaShopConfiguration $prestaShopConfiguration,
        PrestaShopContext $prestaShopContext,
        ShopUuidManager $shopUuidManager,
        LinkAdapter $linkAdapter,
        CacheInterface $cache,
        Token $token,
        Client $client = null,
        OnboardingPayloadBuilder $onboardingPayloadBuilder,
        ShopContext $shopContext
    ) {
        parent::__construct($exceptionHandler, $logger, $prestaShopConfiguration, $prestaShopContext, $shopUuidManager, $linkAdapter, $cache, $token, $client);

        $this->onboardingPayloadBuilder = $onboardingPayloadBuilder;
        $this->shopContext = $shopContext;
    }

    /**
     * Create shop UUID from PSL
     *
     * @return array|false (ResponseApiHandler class)
     */
    public function createShopUuid($correlationId)
    {
        $this->setRoute('/accounts');

        $response = $this->post([
            'headers' => [
                'X-Correlation-Id' => $correlationId,
            ],
            'json' => [],
        ]);

        if (!$this->checkResponse('createShopUuid', $response)) {
            return false;
        }

        $shopUuid = $response['body']['account_id'];

        // Update the shop UUID in DB
        $this->prestaShopConfiguration->set(PsAccount::PS_CHECKOUT_SHOP_UUID_V4, $shopUuid);

        return $shopUuid;
    }

    /**
     * Create shop from PSL
     *
     * @param array $data
     *
     * @return array|false (ResponseApiHandler class)
     */
    public function createShop(Session $session, array $data)
    {
        $this->setRoute('/shops');

        $response = $this->post([
            'headers' => [
                'X-Correlation-Id' => $session->getCorrelationId(),
                'Session-Token' => $session->getAuthToken(),
            ],
            'json' => $data,
        ]);

        if (!$this->checkResponse('createShop', $response)) {
            return false;
        }

        return $response;
    }

    /**
     * Update shop from PSL
     *
     * @param array $data
     *
     * @return array|false (ResponseApiHandler class)
     */
    public function updateShop(Session $session, array $data)
    {
        $this->setRoute('/shops/' . $this->shopUuid);

        $response = $this->patchCall([
            'headers' => [
                'X-Correlation-Id' => $session->getCorrelationId(),
                'Session-Token' => $session->getAuthToken(),
            ],
            'json' => $data,
        ]);

        if (!$this->checkResponse('updateShop', $response)) {
            return false;
        }

        return $response;
    }

    /**
     * Onboard a merchant on PSL (get PayPal onboarding link)
     *
     * @return array|false (ResponseApiHandler class)
     */
    public function onboard(Session $session)
    {
        $this->setRoute('/payments/onboarding/onboard');

        $this->onboardingPayloadBuilder->buildFullPayload();

        if ($this->shopContext->isReady()) {
            $this->onboardingPayloadBuilder->buildMinimalPayload();
        }

        $response = $this->post([
            'headers' => [
                'X-Correlation-Id' => $session->getCorrelationId(),
                'Session-Token' => $session->getAuthToken(),
            ],
            'json' => $this->onboardingPayloadBuilder->presentPayload()->getArray(),
        ]);

        // Retry with minimal payload when full payload failed
        if (substr((string) $response['httpCode'], 0, 1) === '4') {
            $this->onboardingPayloadBuilder->buildMinimalPayload();
            $response = $this->post([
                'headers' => [
                    'X-Correlation-Id' => $session->getCorrelationId(),
                    'Session-Token' => $session->getAuthToken(),
                ],
                'json' => $this->onboardingPayloadBuilder->presentPayload()->getArray(),
            ]);
        }

        if (false === isset($response['body']['links']['1']['href'])) {
            $response['status'] = false;

            return $response;
        }

        if (!$this->checkResponse('onboard', $response)) {
            return false;
        }

        $response['onboardingLink'] = $response['body']['links']['1']['href'];

        return $response;
    }

    /**
     * Force update merchant integrations from PSL
     *
     * @param string $merchantId
     *
     * @return array|false (ResponseApiHandler class)
     */
    public function forceUpdateMerchantIntegrations(Session $session, $merchantId)
    {
        $this->setRoute('/shops/' . $this->shopUuid . '/force-update-merchant-integrations');

        $response = $this->post([
            'headers' => [
                'X-Correlation-Id' => $session->getCorrelationId(),
                'Session-Token' => $session->getAuthToken(),
            ],
            'json' => [
                'merchant_id' => $merchantId,
            ],
        ]);

        if (!$this->checkResponse('forceUpdateMerchantIntegrations', $response)) {
            return false;
        }

        return $response;
    }
}

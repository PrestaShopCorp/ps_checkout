<?php

namespace PrestaShop\Module\PrestashopCheckout\Session\Onboarding;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\PrestashopCheckout\Configuration\PrestaShopConfiguration;
use PrestaShop\Module\PrestashopCheckout\Exception\PsCheckoutSessionException;
use PrestaShop\Module\PrestashopCheckout\PayPal\Mode;
use PrestaShop\Module\PrestashopCheckout\Session\SessionConfiguration;

class OnboardingSessionManagerTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|OnboardingSessionRepository
     */
    private $onboardingSessionRepository;
    /**
     * @var SessionConfiguration
     */
    private $sessionConfiguration;
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|PrestaShopConfiguration
     */
    private $prestaShopConfiguration;
    /**
     * @var \Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $context;
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Ps_checkout
     */
    private $module;
    /**
     * @var OnboardingSessionManager
     */
    private $onboardingSessionManager;

    public function setUp()
    {
        $this->onboardingSessionRepository = $this->createMock(OnboardingSessionRepository::class);
        $this->sessionConfiguration = new SessionConfiguration();
        $this->prestaShopConfiguration = $this->createMock(PrestaShopConfiguration::class);
        $this->context = $this->createMock(\Context::class);
        $this->context->shop->id = 1;
        $this->module = $this->createConfiguredMock(
            \Ps_checkout::class,
            [
                'getLogger' => $this->createMock(\Psr\Log\LoggerInterface::class),
            ]
        );

        $this->prestaShopConfiguration->method('get')->willReturn(Mode::SANDBOX);

        $this->onboardingSessionManager = new OnboardingSessionManager(
            $this->onboardingSessionRepository,
            $this->sessionConfiguration,
            $this->prestaShopConfiguration,
            $this->context,
            $this->module
        );
    }

    /**
     * @dataProvider getStateDataSuccess
     *
     * @param string $next
     * @param array $sessionData
     */
    public function testCanSuccesfullySwitchState($next, array $sessionData)
    {
        $this->onboardingSessionManager->can($next, $sessionData);
    }

    /**
     * @dataProvider getStateDataFail
     *
     * @param string $next
     * @param array $sessionData
     */
    public function testCanFailsStateSwitch($next, array $sessionData)
    {
        $this->expectException(PsCheckoutSessionException::class);
        $this->onboardingSessionManager->can($next, $sessionData);
    }

    public function getStateDataSuccess()
    {
        return [
            [
                'next' => 'start',
                'sessionData' => [
                    'expires_at' => '2021-01-01',
                    'correlation_id' => '',
                    'mode' => '',
                    'user_id' => '',
                    'shop_id' => '',
                    'is_closed' => '',
                    'auth_token' => '',
                    'status' => '',
                    'created_at' => '',
                    'updated_at' => '',
                    'closed_at' => '',
                    'is_sse_opened' => '',
                    'data' => [
                        'account_email' => 'email',
                        'account_id' => 'id',
                    ],
                ],
            ],
        ];
    }

    public function getStateDataFail()
    {
        return [
            [
                'next' => 'collect_shop_data',
                'sessionData' => [
                    'expires_at' => '2021-01-01',
                    'correlation_id' => '',
                    'mode' => '',
                    'user_id' => '',
                    'shop_id' => '',
                    'is_closed' => '',
                    'auth_token' => '',
                    'status' => '',
                    'created_at' => '',
                    'updated_at' => '',
                    'closed_at' => '',
                    'is_sse_opened' => '',
                    'data' => '',
                ],
            ],
        ];
    }
}

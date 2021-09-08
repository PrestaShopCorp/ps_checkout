<?php


use PrestaShop\Module\PrestashopCheckout\Session\SessionManager;
use PHPUnit\Framework\TestCase;

class SessionManagerTest extends TestCase
{
    /**
     * @var PHPUnit_Framework_MockObject_MockObject|\PrestaShop\Module\PrestashopCheckout\Session\Onboarding\OnboardingSessionRepository
     */
    private $sessionRepository;
    /**
     * @var SessionManager
     */
    private $sessionManager;

    public function setUp()
    {
        $this->sessionRepository = $this->createMock(\PrestaShop\Module\PrestashopCheckout\Session\Onboarding\OnboardingSessionRepository::class);
        $this->sessionManager = new SessionManager($this->sessionRepository);
    }

    /**
     * @dataProvider getData
     *
     * @param array $sessionData
     * @param boolean $expired
     */
    public function testGet(array $sessionData, $expired)
    {
        $this->sessionRepository->method('get')->willReturn(new \PrestaShop\Module\PrestashopCheckout\Session\Session($sessionData));
        $this->sessionRepository->method('close')->willReturn(true);
        if ($expired) {
            $this->assertEquals(null, $this->sessionManager->get($sessionData));
        } else {
            $this->assertInstanceOf(\PrestaShop\Module\PrestashopCheckout\Session\Session::class, $this->sessionManager->get($sessionData));
        }
    }

    public function getData()
    {
        return [
            [
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
                'expired' => true
            ],
            [
                'sessionData' => [
                    'expires_at' => '3021-01-01',
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
                'expired' => false
            ]
        ];
    }
}

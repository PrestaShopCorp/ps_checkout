<?php

namespace Tests\Unit\Repository;

use PHPUnit_Framework_MockObject_MockObject;
use PrestaShop\Module\PrestashopCheckout\Configuration\PrestaShopConfiguration;
use PrestaShop\Module\PrestashopCheckout\Context\PrestaShopContext;
use PrestaShop\Module\PrestashopCheckout\OnBoarding\Helper\OnBoardingStatusHelper;
use PrestaShop\Module\PrestashopCheckout\Repository\PsAccountRepository;
use PHPUnit\Framework\TestCase;
use PrestaShop\Module\PrestashopCheckout\ShopUuidManager;
use PrestaShop\Module\PsAccounts\Service\PsAccountsService;
use PrestaShop\PsAccountsInstaller\Installer\Facade\PsAccounts;
use Tests\Unit\PHPUnitUtil;

class PsAccountRepositoryTest extends TestCase
{
    /**
     * @var PHPUnit_Framework_MockObject_MockObject|PrestaShopConfiguration
     */
    private $prestaShopConfiguration;
    /**
     * @var PHPUnit_Framework_MockObject_MockObject|OnBoardingStatusHelper
     */
    private $onBoardingStatusHelper;
    /**
     * @var PHPUnit_Framework_MockObject_MockObject|PsAccounts
     */
    private $psAccountsFacade;
    /**
     * @var PHPUnit_Framework_MockObject_MockObject|PrestaShopContext
     */
    private $psContext;
    /**
     * @var PHPUnit_Framework_MockObject_MockObject|ShopUuidManager
     */
    private $shopUuidManager;
    /**
     * @var PsAccountRepository
     */
    private $psAccountRepository;
    /**
     * @var PHPUnit_Framework_MockObject_MockObject|PsAccountsService
     */
    private $psAccountsService;

    public function setUp()
    {
        parent::setUp();
        $this->prestaShopConfiguration = $this->createMock(PrestaShopConfiguration::class);
        $this->onBoardingStatusHelper = $this->createMock(OnBoardingStatusHelper::class);
        $this->psAccountsFacade = $this->createMock(PsAccounts::class);
        $this->psContext = $this->createMock(PrestaShopContext::class);
        $this->shopUuidManager = $this->createMock(ShopUuidManager::class);

        $this->psAccountRepository = new PsAccountRepository(
                $this->prestaShopConfiguration,
                $this->onBoardingStatusHelper,
                $this->psAccountsFacade,
                $this->psContext,
                $this->shopUuidManager
        );
    }

    public function testUsesPsCheckoutFirebaseAuthenticationData()
    {
        $this->onBoardingStatusHelper->method('isPsAccountsOnboarded')->willReturn(true);
        $this->onBoardingStatusHelper->method('isPsCheckoutOnboarded')->willReturn(true);
        $this->psAccountsFacade->method('getPsAccountsService')->willReturn(true);

        $this->assertFalse(PHPUnitUtil::callMethod($this->psAccountRepository, 'shouldUsePsAccountsData', []));
    }

    public function testUsesPsCheckoutFirebaseAuthenticationDataWhenPsAccountsNotInstalled()
    {
        $this->onBoardingStatusHelper->method('isPsAccountsOnboarded')->willReturn(false);
        $this->onBoardingStatusHelper->method('isPsCheckoutOnboarded')->willReturn(true);
        $this->psAccountsFacade->method('getPsAccountsService')->willThrowException(new \Exception());

        $this->assertFalse(PHPUnitUtil::callMethod($this->psAccountRepository, 'shouldUsePsAccountsData', []));
    }

    public function testUsesPsAccountFirebaseAuthenticationData()
    {
        $this->onBoardingStatusHelper->method('isPsAccountsOnboarded')->willReturn(true);
        $this->onBoardingStatusHelper->method('isPsCheckoutOnboarded')->willReturn(false);
        $this->psAccountsFacade->method('getPsAccountsService')->willReturn(true);

        $this->assertTrue(PHPUnitUtil::callMethod($this->psAccountRepository, 'shouldUsePsAccountsData', []));
    }
}

<?php

namespace Tests\Unit\Helper;

use PrestaShop\Module\PrestashopCheckout\Configuration\PrestaShopConfiguration;
use PrestaShop\Module\PrestashopCheckout\Entity\PsAccount;
use PrestaShop\Module\PrestashopCheckout\OnBoarding\Helper\OnboardingStatusHelper;
use PrestaShop\PsAccountsInstaller\Installer\Facade\PsAccounts;
use Tests\Unit\Mock\MockedPsAccountsServiceTestCase;

class OnBoardingStatusHelperTest extends MockedPsAccountsServiceTestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|PrestaShopConfiguration
     */
    private $configuration;
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|PsAccounts
     */
    private $psAccountsFacade;
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\stdclass
     */
    private $psAccountsService;
    /**
     * @var OnboardingStatusHelper
     */
    private $onBoardingStatusHelper;

    public function setUp()
    {
        parent::setUp();

        $this->configuration = $this->createMock(PrestaShopConfiguration::class);
        $this->psAccountsFacade = $this->createMock(PsAccounts::class);
        $this->psAccountsService = $this->getPsAccountsServiceMock();

        $this->onBoardingStatusHelper = new OnboardingStatusHelper($this->configuration, $this->psAccountsFacade);
    }

    public function testPsCheckoutOnboarded()
    {
        $this->configuration
            ->expects($this->once())
            ->method('get')
            ->with(PsAccount::PS_PSX_FIREBASE_ID_TOKEN)
            ->will($this->returnValue('TOKEN'));

        $this->assertTrue($this->onBoardingStatusHelper->isPsCheckoutOnboarded());
    }

    public function testPsCheckoutNotOnboarded()
    {
        $this->configuration
            ->expects($this->once())
            ->method('get')
            ->with(PsAccount::PS_PSX_FIREBASE_ID_TOKEN)
            ->will($this->returnValue(''));

        $this->assertFalse($this->onBoardingStatusHelper->isPsCheckoutOnboarded());
    }

    public function testPsAccountsOnboarded()
    {
        $this->psAccountsFacade
            ->expects($this->once())
            ->method('getPsAccountsService')
            ->will($this->returnValue($this->psAccountsService));

        $this->psAccountsService
        ->expects($this->once())
        ->method('isAccountLinked')
        ->will($this->returnValue(true));

        $this->assertTrue($this->onBoardingStatusHelper->isPsAccountsOnboarded());
    }

    public function testPsAccountsNotOnboarded()
    {
        $this->psAccountsFacade
            ->expects($this->once())
            ->method('getPsAccountsService')
            ->will($this->returnValue($this->psAccountsService));

        $this->psAccountsService
            ->expects($this->once())
            ->method('isAccountLinked')
            ->will($this->returnValue(false));

        $this->assertFalse($this->onBoardingStatusHelper->isPsAccountsOnboarded());
    }

    public function testPsAccountsNotInstalled()
    {
        $this->psAccountsFacade
            ->expects($this->once())
            ->method('getPsAccountsService')
            ->will($this->throwException(new \Exception()));

        $this->assertFalse($this->onBoardingStatusHelper->isPsAccountsOnboarded());
    }
}

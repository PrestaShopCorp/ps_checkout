<?php

namespace Integration\Settings\Configuration;

use PsCheckout\Core\Settings\Configuration\PayPalSdkConfiguration;
use PsCheckout\Core\Tests\Integration\BaseTestCase;

/**
 * @coversDefaultClass \PsCheckout\Core\Settings\Configuration\PayPalSdkConfiguration
 */
class PayPalSdkConfigurationTest extends BaseTestCase
{
    /**
     * @var PayPalSdkConfiguration
     */
    private $paypalSdkConfiguration;

    protected function setUp(): void
    {
        parent::setUp();

        $this->paypalSdkConfiguration = $this->getService(PayPalSdkConfiguration::class);
    }

    /**
     * @covers ::buildConfiguration
     */
    public function testBuildConfiguration(): void
    {
        $configuration = $this->paypalSdkConfiguration->buildConfiguration();

        self::assertEquals([], $configuration);
    }
}

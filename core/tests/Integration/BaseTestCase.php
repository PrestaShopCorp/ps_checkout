<?php

namespace PsCheckout\Core\Tests\Integration;

use PHPUnit\Framework\TestCase;
use PrestaShop\PrestaShop\Adapter\ContainerBuilder;
use PrestaShop\PrestaShop\Adapter\SymfonyContainer;

class BaseTestCase extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        \Db::getInstance()->execute('START TRANSACTION;');

        self::clearCache();

        // Some tests might have cleared the configuration
        \Configuration::loadConfiguration();
        $this->updateConfigurationValues();

        \Cache::clear();
    }

    protected function tearDown(): void
    {
        // Rollback transaction
        \Db::getInstance()->execute('ROLLBACK;');

        parent::tearDown();
    }

    private static function clearCache()
    {
        if (method_exists(\Cache::class, 'clear')) {
            \Cache::clear();
        }

        if (method_exists(\Cache::class, 'clean')) {
            \Cache::clean('*');
        }

        if (method_exists(\Cart::class, 'resetStaticCache')) {
            \Cart::resetStaticCache();
        }

        if (method_exists(\TaxManagerFactory::class, 'resetStaticCache')) {
            \TaxManagerFactory::resetStaticCache();
        }

        if (method_exists(\Address::class, 'resetStaticCache')) {
            \Address::resetStaticCache();
        }

        if (method_exists(\Carrier::class, 'resetStaticCache')) {
            \Carrier::resetStaticCache();
        }

        if (method_exists(\CartRule::class, 'resetStaticCache')) {
            \CartRule::resetStaticCache();
        }

        if (method_exists(\Currency::class, 'resetStaticCache')) {
            \Currency::resetStaticCache();
        }

        if (method_exists(\GroupReduction::class, 'resetStaticCache')) {
            \GroupReduction::resetStaticCache();
        }

        if (method_exists(\Pack::class, 'resetStaticCache')) {
            \Pack::resetStaticCache();
        }

        if (method_exists(\Product::class, 'resetStaticCache')) {
            \Product::resetStaticCache();
        }

        if (method_exists(\Product::class, 'flushPriceCache')) {
            \Product::flushPriceCache();
        }

        if (method_exists(\Combination::class, 'resetStaticCache')) {
            \Combination::resetStaticCache();
        }

        if (method_exists(\Tools::class, 'resetStaticCache')) {
            \Tools::resetStaticCache();
        }

        if (method_exists(\Tab::class, 'resetStaticCache')) {
            \Tab::resetStaticCache();
        }

        if (method_exists(\SpecificPrice::class, 'flushCache')) {
            \SpecificPrice::flushCache();
        }
    }

    protected function updateConfigurationValues()
    {
        $configurations = [
            'CONF_PS_CHECKOUT_FIXED' => 0.2,
            'CONF_PS_CHECKOUT_VAR' => 2,
            'CONF_PS_CHECKOUT_FIXED_FOREIGN' => 0.2,
            'CONF_PS_CHECKOUT_VAR_FOREIGN' => 2,
            'PS_CHECKOUT_INTENT' => 'CAPTURE',
            'PS_CHECKOUT_MODE' => 'SANDBOX',
            'PS_CHECKOUT_PAYPAL_ID_MERCHANT' => 'HDVL5SJRCQVGL',
            'PS_CHECKOUT_PAYPAL_EMAIL_MERCHANT' => 'CheckoutSandbox@business.example.com',
            'PS_CHECKOUT_PAYPAL_EMAIL_STATUS' => 1,
            'PS_CHECKOUT_PAYPAL_PAYMENT_STATUS' => 1,
            'PS_CHECKOUT_CARD_PAYMENT_STATUS' => 'SUBSCRIBED',
            'PS_CHECKOUT_CARD_PAYMENT_ENABLED' => 1,
            'PS_CHECKOUT_LOGGER_MAX_FILES' => 15,
            'PS_CHECKOUT_LOGGER_LEVEL' => 400,
            'PS_CHECKOUT_LOGGER_HTTP' => 0,
            'PS_CHECKOUT_LOGGER_HTTP_FORMAT' => 'DEBUG',
            'PS_CHECKOUT_INTEGRATION_DATE' => '2024-04-01',
            'PS_CHECKOUT_DISPLAY_LOGO_PRODUCT' => 1,
            'PS_CHECKOUT_DISPLAY_LOGO_CART' => 1,
            'PS_CHECKOUT_HOSTED_FIELDS_CONTINGENCIES' => 'SCA_WHEN_REQUIRED',
            'PS_CHECKOUT_PAYPAL_BUTTON' => json_encode(['shape' => 'pill', 'label' => 'pay', 'color' => 'gold']),
            'PS_CHECKOUT_STATE_COMPLETED' => 2,
            'PS_CHECKOUT_STATE_CANCELED' => 6,
            'PS_CHECKOUT_STATE_ERROR' => 8,
            'PS_CHECKOUT_STATE_REFUNDED' => 7,
            'PS_CHECKOUT_STATE_PENDING' => 14,
            'PS_CHECKOUT_STATE_PARTIALLY_REFUNDED' => 15,
            'PS_CHECKOUT_STATE_PARTIALLY_PAID' => 16,
            'PS_CHECKOUT_STATE_AUTHORIZED' => 17,
            'PS_CHECKOUT_SHOP_UUID_V4' => '0ff69539-e7fd-43df-a8b1-3bfc1f34fac4',
            'PS_CHECKOUT_PAYPAL_COUNTRY_MERCHANT' => 'FR',
            'PS_CHECKOUT_PAY_PAL' => 1,
            'PS_CHECKOUT_PAY_LATER' => 1,
            'PS_CHECKOUT_BRANDED_CARD' => 1,
            'PS_CHECKOUT_CUSTOM_CARD' => 1,
            'PS_CHECKOUT_GOOGLE_PAY' => 1,
            'PS_CHECKOUT_APPLE_PAY' => 1,
            'PS_CHECKOUT_test' => 1,
        ];

        foreach ($configurations as $key => $value) {
            \Configuration::updateValue($key, $value);
        }

        \Configuration::updateValue('PS_INVOICE', false);
    }

    protected function getService($serviceName)
    {
        $containerFront = ContainerBuilder::getContainer('front', true);

        $service = $containerFront->get($serviceName);

        if (!$service) {
            $containerAdmin = SymfonyContainer::getInstance();
            $service = $containerAdmin->get($serviceName);
        }

        return $service;
    }
}

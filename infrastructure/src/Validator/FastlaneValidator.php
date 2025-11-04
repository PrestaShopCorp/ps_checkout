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

namespace PsCheckout\Infrastructure\Validator;

use PsCheckout\Core\Settings\Configuration\PayPalFastlaneConfiguration;
use PsCheckout\Infrastructure\Adapter\ConfigurationInterface;
use PsCheckout\Infrastructure\Adapter\ContextInterface;
use PsCheckout\Infrastructure\Adapter\ToolsInterface;

class FastlaneValidator implements FastlaneValidatorInterface
{
    /**
     * @var ContextInterface
     */
    private $context;

    /**
     * @var ConfigurationInterface
     */
    private $configuration;

    /**
     * @var ToolsInterface
     */
    private $tools;

    public function __construct(
        ContextInterface $context,
        ConfigurationInterface $configuration,
        ToolsInterface $tools
    ) {
        $this->context = $context;
        $this->configuration = $configuration;
        $this->tools = $tools;
    }

    /**
     * {@inheritDoc}
     */
    public function shouldLoadFastlane(): bool
    {
        $controller = $this->tools->getValue('controller');

        if (empty($controller) && isset($this->context->getController()->php_self)) {
            $controller = $this->context->getController()->php_self;
        }

        if (
            $this->configuration->getBoolean(PayPalFastlaneConfiguration::PS_CHECKOUT_FASTLANE_ENABLED) &&
            $controller === 'order' &&
            !$this->context->getCustomer()->isLogged()
        ) {
            return true;
        }

        return false;
    }
}

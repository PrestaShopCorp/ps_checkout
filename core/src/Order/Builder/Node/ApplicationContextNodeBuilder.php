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

namespace PsCheckout\Core\Order\Builder\Node;

use PsCheckout\Infrastructure\Adapter\ConfigurationInterface;
use PsCheckout\Infrastructure\Adapter\LinkInterface;

class ApplicationContextNodeBuilder implements ApplicationContextNodeBuilderInterface
{
    /**
     * @var bool
     */
    private $isExpressCheckout;

    /**
     * @var ConfigurationInterface
     */
    private $configuration;

    /**
     * @var LinkInterface
     */
    private $link;

    public function __construct(
        ConfigurationInterface $configuration,
        LinkInterface $link
    ) {
        $this->configuration = $configuration;
        $this->link = $link;
    }

    /**
     * {@inheritDoc}
     */
    public function build(): array
    {
        $node['application_context'] = [
            'brand_name' => $this->configuration->get('PS_SHOP_NAME'),
            'shipping_preference' => $this->isExpressCheckout ? 'GET_FROM_FILE' : 'SET_PROVIDED_ADDRESS',
            'return_url' => $this->link->getModuleLink('validate'),
            'cancel_url' => $this->link->getModuleLink('cancel'),
        ];

        return $node;
    }

    /**
     * {@inheritDoc}
     */
    public function setIsExpressCheckout(bool $isExpressCheckout): ApplicationContextNodeBuilder
    {
        $this->isExpressCheckout = $isExpressCheckout;

        return $this;
    }
}

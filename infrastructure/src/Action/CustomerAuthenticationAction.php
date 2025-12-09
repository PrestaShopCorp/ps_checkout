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

namespace PsCheckout\Infrastructure\Action;

use PsCheckout\Core\Customer\Request\ValueObject\ExpressCheckoutRequest;
use PsCheckout\Core\Exception\PsCheckoutException;
use PsCheckout\Infrastructure\Adapter\ConfigurationInterface;
use PsCheckout\Infrastructure\Adapter\ContextInterface;
use PsCheckout\Infrastructure\Adapter\CustomerInterface;

class CustomerAuthenticationAction implements CustomerAuthenticationActionInterface
{
    /**
     * @var ConfigurationInterface
     */
    private $configuration;

    /**
     * @var CustomerInterface
     */
    private $customer;

    /**
     * @var ContextInterface
     */
    private $context;

    public function __construct(
        ConfigurationInterface $configuration,
        CustomerInterface $customer,
        ContextInterface $context
    ) {
        $this->configuration = $configuration;
        $this->customer = $customer;
        $this->context = $context;
    }

    /**
     * @param ExpressCheckoutRequest $expressCheckoutRequest
     *
     * @return void
     *
     * @throws PsCheckoutException
     */
    public function execute(ExpressCheckoutRequest $expressCheckoutRequest)
    {
        throw new PsCheckoutException('CustomerAuthenticationAction is deprecated and should not be used anymore.');
    }
}

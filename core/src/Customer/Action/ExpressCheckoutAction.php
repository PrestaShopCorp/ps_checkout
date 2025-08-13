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

namespace PsCheckout\Core\Customer\Action;

use PsCheckout\Core\Customer\Request\ValueObject\ExpressCheckoutRequest;
use PsCheckout\Infrastructure\Action\CreateOrUpdateAddressActionInterface;
use PsCheckout\Infrastructure\Action\CustomerAuthenticationActionInterface;
use PsCheckout\Infrastructure\Adapter\ContextInterface;

class ExpressCheckoutAction implements ExpressCheckoutActionInterface
{
    /**
     * @var ContextInterface
     */
    private $context;

    /**
     * @var CustomerAuthenticationActionInterface
     */
    private $customerAuthenticationAction;

    /**
     * @var CreateOrUpdateAddressActionInterface
     */
    private $createOrUpdateAddressAction;

    public function __construct(
        ContextInterface $context,
        CustomerAuthenticationActionInterface $customerAuthenticationAction,
        CreateOrUpdateAddressActionInterface $createOrUpdateAddressAction
    ) {
        $this->context = $context;
        $this->customerAuthenticationAction = $customerAuthenticationAction;
        $this->createOrUpdateAddressAction = $createOrUpdateAddressAction;
    }

    public function execute(ExpressCheckoutRequest $expressCheckoutRequest)
    {
        if ($this->context->getCustomer() && !$this->context->getCustomer()->isLogged()) {
            $this->customerAuthenticationAction->execute($expressCheckoutRequest);
        }

        $this->context->resetContextCartAddresses();

        $this->createOrUpdateAddressAction->execute($expressCheckoutRequest);
    }
}

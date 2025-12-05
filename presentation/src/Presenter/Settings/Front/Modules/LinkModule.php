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

namespace PsCheckout\Presentation\Presenter\Settings\Front\Modules;

use PsCheckout\Infrastructure\Adapter\LinkInterface;
use PsCheckout\Infrastructure\Adapter\ToolsInterface;
use PsCheckout\Presentation\Presenter\PresenterInterface;

class LinkModule implements PresenterInterface
{
    /**
     * @var string
     */
    private $moduleName;

    /**
     * @var LinkInterface
     */
    private $link;

    /**
     * @var ToolsInterface
     */
    private $tools;

    public function __construct(
        string $moduleName,
        LinkInterface $link,
        ToolsInterface $tools
    ) {
        $this->moduleName = $moduleName;
        $this->link = $link;
        $this->tools = $tools;
    }

    public function present(): array
    {
        $params = [
            'token' => $this->tools->getToken(false),
        ];

        return [
            $this->moduleName . 'CreateUrl' => $this->link->getModuleLink('create', $params),
            $this->moduleName . 'CheckUrl' => $this->link->getModuleLink('check', $params),
            $this->moduleName . 'ValidateUrl' => $this->link->getModuleLink('validate', $params),
            $this->moduleName . 'CancelUrl' => $this->link->getModuleLink('cancel', $params),
            $this->moduleName . 'ExpressCheckoutUrl' => $this->link->getModuleLink('ExpressCheckout', $params),
            $this->moduleName . 'VaultUrl' => $this->link->getModuleLink('vault', $params),
            $this->moduleName . 'PaymentUrl' => $this->link->getModuleLink('payment', $params),
            $this->moduleName . 'GooglePayUrl' => $this->link->getModuleLink('googlepay', $params),
            $this->moduleName . 'ApplePayUrl' => $this->link->getModuleLink('applepay', $params),
            $this->moduleName . 'CheckoutUrl' => $this->link->getPageLink('order'),
            $this->moduleName . 'ConfirmUrl' => $this->link->getPageLink('order-confirmation'),
        ];
    }
}

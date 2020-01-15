<?php
/**
* 2007-2019 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2019 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class CreatePaypalOrderHandler
{
    /**
     * Prestashop context object
     *
     * @var \Context
     */
    private $context;

    public function __construct(\Context $context)
    {
        $this->setContext($context);
    }

    public function handle(CreateEmptyCustomerCartCommand $command)
    {
        // Present an improved cart in order to create the payload
        $cartPresenter = new CartPresenter($this->context);
        $cartPresenter = $cartPresenter->present();

        // Create the payload
        $builder = new OrderPayloadBuilder($cartPresenter);
        $builder->buildFullPayload();
        $payload = $builder->presentPayload()->getJson();

        // Create the paypal order
        $paypalOrder = (new Order($this->context->link))->create($payload);

        // Retry with minimal payload when full payload failed
        if (substr((string) $paypalOrder['httpCode'], 0, 1) === '4') {
            $builder->buildMinimalPayload();
            $payload = $builder->presentPayload()->getJson();
            $paypalOrder = (new Order($this->context->link))->create($payload);
        }
    }

    /**
     * Setter for context
     *
     * @param \Context $context
     */
    public function setContext(\Context $context)
    {
        $this->context = $context;
    }
}

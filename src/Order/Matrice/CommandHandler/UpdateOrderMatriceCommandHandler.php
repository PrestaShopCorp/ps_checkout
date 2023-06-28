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

namespace PrestaShop\Module\PrestashopCheckout\Order\Matrice\CommandHandler;

use PrestaShop\Module\PrestashopCheckout\Event\EventDispatcherInterface;
use PrestaShop\Module\PrestashopCheckout\Order\Matrice\Command\UpdateOrderMatriceCommand;
use PrestaShop\Module\PrestashopCheckout\Order\Matrice\Event\OrderMatriceUpdatedEvent;

class UpdateOrderMatriceCommandHandler
{
    private $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @throws \PrestaShopException
     * @throws \PrestaShopDatabaseException
     */
    public function handle(UpdateOrderMatriceCommand $command)
    {
        $orderMatrice = new \OrderMatrice();
        $orderMatrice->id_order_prestashop = $command->getOrderId()->getValue();
        $orderMatrice->id_order_paypal = $command->getOrderPayPalId()->getValue();

        $res = $orderMatrice->add();

        if (!empty($res)) {
            $this->eventDispatcher->dispatch(new OrderMatriceUpdatedEvent());
        }
    }
}

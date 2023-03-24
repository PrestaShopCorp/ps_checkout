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

namespace PrestaShop\Module\PrestashopCheckout\Order\CommandHandler;

use PrestaShop\Module\PrestashopCheckout\Order\Command\UpdatePayPalOrderMatriceCommand;
use PrestaShop\Module\PrestashopCheckout\Order\Exception\OrderException;

class UpdatePayPalOrderMatriceCommandHandler
{
    public function handle(UpdatePayPalOrderMatriceCommand $updatePayPalOrderMatriceCommand)
    {
        /** @var \Ps_checkout $module */
        $module = \Module::getInstanceByName('ps_checkout');

        $paypalOrderId = $updatePayPalOrderMatriceCommand->getPayPalOrderId()->getValue();
        if (false === $this->setOrdersMatrice($module->currentOrder, $paypalOrderId)) {
            throw new OrderException(sprintf('Set Order Matrice error for Prestashop Order ID : %s and Paypal Order ID : %s', $module->currentOrder, $paypalOrderId), OrderException::ORDER_MATRICE_ERROR);
        }
    }

    /**
     * @todo To remove when need of fallback on previous version is gone
     *
     * Set the matrice order values
     *
     * @param int $orderPrestashopId from prestashop
     * @param string $orderPaypalId PayPal order id
     *
     * @return bool
     *
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    private function setOrdersMatrice($orderPrestashopId, $orderPaypalId)
    {
        $orderMatrice = new \OrderMatrice();
        $orderMatrice->id_order_prestashop = $orderPrestashopId;
        $orderMatrice->id_order_paypal = $orderPaypalId;

        return $orderMatrice->add();
    }
}

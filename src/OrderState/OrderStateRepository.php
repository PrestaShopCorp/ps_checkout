<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */

namespace PrestaShop\Module\PrestashopCheckout\OrderState;

use PrestaShop\Module\PrestashopCheckout\OrderState\Exception\OrderStateException;
use PrestaShop\PrestaShop\Adapter\Entity\PrestaShopCollection;

class OrderStateRepository
{
    /**
     * @var string
     */
    private $moduleName;

    /**
     * @param string $moduleName
     */
    public function __construct($moduleName)
    {
        $this->moduleName = $moduleName;
    }

    /**
     * Add new orderStates or update them if they already exists
     *
     * @param OrderState[] $orderStates
     *
     * @throws OrderStateException
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function add($orderStates)
    {
        foreach ($orderStates as $orderState) {
            if (!$this->exist($orderState->getConfigurationKey())) {
                // add
                $this->addNewOrderState($orderState);
            } else {
                // update
                $this->updateExistingOrderState($orderState);
            }
        }
    }

    /**
     * Delete specific order states and its configuration
     *
     * @param string[] $configurationKeys
     *
     * @return bool
     *
     * @throws OrderStateException
     * @throws \PrestaShopException
     */
    public function deleteOrderStates($configurationKeys)
    {
        $result = true;
        foreach ($configurationKeys as $configurationKey) {
            $orderStateId = $this->getOrderStateId($configurationKey);
            $orderState = $this->getOrderStateById($orderStateId);

            $orderState->deleted = true;
            $result = $result && (bool) $orderState->save() && (bool) $this->deleteOrderStateConfig($configurationKey);
        }

        return $result;
    }

    /**
     * Delete all the order states created with the module name
     *
     * @return bool
     *
     * @throws \PrestaShopException
     */
    public function deleteAllOrderStates()
    {
        $result = true;

        $orderStateCollection = new PrestaShopCollection('OrderState');
        $orderStateCollection->where('module_name', '=', $this->moduleName);
        /** @var \PrestaShop\PrestaShop\Adapter\Entity\OrderState[] $orderStates */
        $orderStates = $orderStateCollection->getAll();

        foreach ($orderStates as $orderState) {
            $orderState->deleted = true;
            $result = $result && (bool) $orderState->save();
        }

        return $result;
    }

    /**
     * @param string $configurationKey
     *
     * @return int
     *
     * @throws OrderStateException
     */
    public function getOrderStateId($configurationKey)
    {
        $orderStateId = (int) \Configuration::getGlobalValue($configurationKey);
        if ($orderStateId === 0) {
            throw new OrderStateException(sprintf('The configuration key is not valid : %s', $configurationKey), OrderStateException::ORDER_STATE_INVALID_CONFIGURATION_KEY);
        }

        return $orderStateId;
    }

    /**
     * @param string $configurationKey
     *
     * @return OrderState
     *
     * @throws OrderStateException
     * @throws \PrestaShopException
     */
    public function getOrderState($configurationKey)
    {
        $orderStateId = $this->getOrderStateId($configurationKey);
        $orderStatePS = $this->getOrderStateById($orderStateId);

        $orderState = new OrderState($configurationKey, [], $orderStatePS->color);
        $orderState->setLogable($orderStatePS->logable);
        $orderState->setPaid($orderStatePS->paid);
        $orderState->setInvoice($orderStatePS->invoice);
        $orderState->setShipped($orderStatePS->shipped);
        $orderState->setDelivery($orderStatePS->delivery);
        $orderState->setPdfDelivery($orderStatePS->pdf_delivery);
        $orderState->setPdfInvoice($orderStatePS->pdf_invoice);
        $orderState->setSendMail($orderStatePS->send_email);
        $orderState->setHidden($orderStatePS->hidden);
        $orderState->setUnremovable($orderStatePS->unremovable);
        $orderState->setTemplate($orderStatePS->template);
        $orderState->setDeleted($orderStatePS->deleted);
        $orderState->setId($orderStateId);

        return $orderState;
    }

    /**
     * @param OrderState $orderState
     *
     * @throws OrderStateException
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    private function updateExistingOrderState($orderState)
    {
        $orderStateId = $this->getOrderStateId($orderState->getConfigurationKey());
        $orderStatePS = $this->getOrderStateById($orderStateId);

        $orderStatePS->name = $orderState->getName();
        $orderStatePS->color = $orderState->getColor();
        $orderStatePS->logable = $orderState->isLogable();
        $orderStatePS->paid = $orderState->isPaid();
        $orderStatePS->invoice = $orderState->isInvoice();
        $orderStatePS->shipped = $orderState->isShipped();
        $orderStatePS->delivery = $orderState->isDelivery();
        $orderStatePS->pdf_delivery = $orderState->isPdfDelivery();
        $orderStatePS->pdf_invoice = $orderState->isPdfInvoice();
        $orderStatePS->send_email = $orderState->isSendMail();
        $orderStatePS->hidden = $orderState->isHidden();
        $orderStatePS->unremovable = $orderState->isUnremovable();
        $orderStatePS->template = $orderState->getTemplate();
        $orderStatePS->deleted = $orderState->isDeleted();

        $result = (bool) $orderStatePS->update();

        if (false === $result) {
            throw new OrderStateException(sprintf('Failed to update OrderState %s', $orderState->getConfigurationKey()), OrderStateException::ORDER_STATE_NOT_UPDATED);
        }
    }

    /**
     * @param OrderState $orderState
     *
     * @throws OrderStateException
     */
    private function addNewOrderState($orderState)
    {
        // save the new order State
        $orderState->save($this->moduleName);

        // save the key in the configuration
        $result = (bool) \Configuration::updateGlobalValue($orderState->getConfigurationKey(), (int) $orderState->getId());
        if (false === $result) {
           throw new OrderStateException(sprintf('Failed to save OrderState %s to Configuration', $orderState->getConfigurationKey()), OrderStateException::ORDER_STATE_CONFIGURATION_NOT_SAVED);
        }
    }

    /**
     * @param string $configurationKey
     *
     * @return bool
     */
    private function exist($configurationKey)
    {
        $orderStateId = (int) \Configuration::getGlobalValue($configurationKey);
        // test if the configuration key exist -> the function return 0 by default if there is no value in DB
        return $orderStateId === 0;
    }

    /**
     * @param int $orderStateId
     *
     * @return \PrestaShop\PrestaShop\Adapter\Entity\OrderState
     *
     * @throws OrderStateException
     * @throws \PrestaShopException
     */
    private function getOrderStateById($orderStateId)
    {
        $orderStateCollection = new PrestaShopCollection('OrderState');
        $orderStateCollection->where('id_order_state', '=', $orderStateId);

        /** @var \PrestaShop\PrestaShop\Adapter\Entity\OrderState $orderStatePS */
        $orderStatePS = $orderStateCollection->getFirst();

        if ($orderStatePS === false) {
            throw new OrderStateException(sprintf('No OrderState for the id : %s', $orderStateId), OrderStateException::ORDER_STATE_INVALID_ID);
        }

        return $orderStatePS;
    }

    /**
     * @param string $configurationKey
     *
     * @return bool
     *
     * @throws OrderStateException
     */
    private function deleteOrderStateConfig($configurationKey)
    {
        $result = (bool) \Configuration::deleteByName($configurationKey);
        if ($result === false) {
            throw new OrderStateException(sprintf("Can't delete the OrderState Configuration %s", $configurationKey), OrderStateException::ORDER_STATE_CONFIGURATION_NOT_DELETED);
        }

        return $result;
    }
}

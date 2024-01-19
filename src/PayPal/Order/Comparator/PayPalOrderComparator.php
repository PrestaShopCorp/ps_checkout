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

namespace PrestaShop\Module\PrestashopCheckout\PayPal\Order\Comparator;

use PrestaShop\Module\PrestashopCheckout\PayPal\Order\ValueObject\PayPalOrder;

class PayPalOrderComparator
{
    /**
     * @param PayPalOrder $oldOrder
     * @param PayPalOrder $newOrder
     *
     * @return array
     */
    public function getFieldsToUpdate(PayPalOrder $oldOrder, PayPalOrder $newOrder)
    {
        $fieldsToUpdate = [];
        $oldOrderArray = $oldOrder->toArray();
        $newOrderArray = $newOrder->toArray();

        $fieldsToUpdate = array_filter($newOrderArray, function ($value, $key) use ($oldOrderArray) {
            return $this->compareValues($value, $oldOrderArray[$key]);
        }, ARRAY_FILTER_USE_BOTH);

//        if ($oldOrder->getIntent() !== $newOrder->getIntent()) {
//            $fieldsToUpdate['intent'] = $newOrder->getIntent();
//        }
//
//
//        if ($this->compareArraysDeep($oldOrderArray['payer'], $newOrderArray['payer'])) {
//            $fieldsToUpdate['payer'] = $newOrderArray['payer'];
//        }
//
//        if ($this->compareArraysDeep($oldOrderArray['purchase_units'], $newOrderArray['purchase_units'])) {
//            $fieldsToUpdate['purchase_units'] = $newOrderArray['purchase_units'];
//        }

        return $fieldsToUpdate;
    }

    private function compareValues($value1, $value2)
    {
        $result = false;
        if (is_array($value1) && is_array($value2)) {
            $result |= $this->compareArraysDeep($value1, $value2);
        } else {
            $result |= $value1 !== $value2;
        }

        return $result;
    }

    /**
     * @param array $array1
     * @param array $array2
     *
     * @return false|int|string
     */
    private function compareArraysDeep($array1, $array2)
    {
        $result = false;

        if (count($array1) !== count($array2)) {
            return true;
        }

        foreach ($array1 as $key => $oldValue) {
            $newValue = $array2[$key];
            if (is_array($oldValue)) {
                $result |= $this->compareArraysDeep($oldValue, $newValue);
            } else {
                $result |= $oldValue !== $newValue;
            }
        }

        return $result;
    }
}

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

namespace PrestaShop\Module\PrestashopCheckout\Serializer;

interface ObjectSerializerInterface
{
    /**
     * @param mixed $data
     * @param string $format
     * @param bool $skipNullValues
     * @param bool $convertToSnakeCase
     * @param array $context
     *
     * @return string
     */
    public function serialize($data, $format, $skipNullValues = false, $convertToSnakeCase = false, array $context = []);

    /**
     * @template T
     *
     * @param string|array $data
     * @param class-string<T> $type //Class of the object created. For example CreatePayPalOrderResponse::class
     * @param string $format //Format of the data passed. For example JsonEncoder::FORMAT
     * @param array $context //Additional parameters. For example skip null values and etc.
     *
     * @return T
     */
    public function deserialize($data, $type, $format, array $context = []);

    /**
     * @param mixed $data
     * @param bool $skipNullValues
     * @param bool $convertToSnakeCase
     * @param array $context
     *
     * @return array
     */
    public function toArray($data, $skipNullValues = false, $convertToSnakeCase = false, array $context = []);
}

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

use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class ObjectSerializer
{
    const PS_SKIP_NULL_VALUES = 'skip_null_values';
    private $serializer;

    public function __construct()
    {
        $this->serializer = new Serializer(
            [
                new ObjectNormalizer(null, null, null, new PropertyInfoExtractor([], [new PhpDocExtractor(), new ReflectionExtractor()])),
                new ArrayDenormalizer(),
            ],
            [new JsonEncoder()]);
    }

    /**
     * @param mixed $data
     * @param string $format
     * @param bool $skipNullValues
     * @param array $context
     *
     * @return string
     */
    public function serialize($data, $format, $skipNullValues = false, array $context = [])
    {
        $childContext = [!defined('\Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer::SKIP_NULL_VALUES') ? self::PS_SKIP_NULL_VALUES : AbstractObjectNormalizer::SKIP_NULL_VALUES => $skipNullValues];

        return $this->serializer->serialize($data, $format, array_replace($context, $childContext));
    }

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
    public function deserialize($data, $type, $format, array $context = [])
    {
        return $this->serializer->deserialize($data, $type, $format, $context);
    }
}

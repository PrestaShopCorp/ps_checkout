<?php

namespace PrestaShop\Module\PrestashopCheckout\Serializer;

use PrestaShop\Module\PrestashopCheckout\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer as SymfonyObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

class ObjectSerializer implements SerializerInterface
{
    private $serializer;

    public function __construct()
    {
        $this->serializer = new Serializer(
            [
                new ObjectNormalizer(
                    new SymfonyObjectNormalizer(
                        null,
                        null,
                        null,
                        new PropertyInfoExtractor([], [new PhpDocExtractor(), new ReflectionExtractor()]))),
                new ArrayDenormalizer()
            ],
            [new JsonEncoder()]);
    }

    /**
     * @param $data
     * @param $format
     * @param array $context
     * @return string
     */
    public function serialize($data, $format, array $context = [])
    {
        return $this->serializer->serialize($data, $format, $context);
    }

    /**
     * @param $data
     * @param $type //Class of the object created. For example CreatePayPalOrderResponse::class
     * @param $format //Format of the data passed. For example JsonEncoder::FORMAT
     * @param array $context //Additional parameters. For example skip null values and etc.
     * @return array|object|string
     */
    public function deserialize($data, $type, $format, array $context = [])
    {
        return $this->serializer->deserialize($data, $type, $format, $context);
    }
}

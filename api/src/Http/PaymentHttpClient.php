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

namespace PsCheckout\Api\Http;

use GuzzleHttp\Psr7\Request;
use Http\Client\Exception\HttpException;
use PsCheckout\Api\Dto\PayPal\ErrorResponseDto;
use PsCheckout\Api\Dto\PayPal\Payment\CaptureRequestDto;
use PsCheckout\Api\Dto\PayPal\Payment\CaptureResponseDto;
use PsCheckout\Api\Dto\PayPal\Payment\GetAuthorizationResponseDto;
use PsCheckout\Api\Dto\PayPal\Payment\ReauthorizeRequestDto;
use PsCheckout\Api\Dto\PayPal\Payment\ReauthorizeResponseDto;
use PsCheckout\Api\Dto\PayPal\Payment\RefundRequestDto;
use PsCheckout\Api\Dto\PayPal\Payment\RefundResponseDto;
use PsCheckout\Api\Dto\PayPal\Payment\VoidAuthorizationResponseDto;
use PsCheckout\Api\Http\Configuration\HttpClientConfigurationBuilderInterface;
use PsCheckout\Api\Http\Exception\PayPalError;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

class PaymentHttpClient extends PsrHttpClientAdapter implements PaymentHttpClientInterface
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    public function __construct(
        HttpClientConfigurationBuilderInterface $configurationBuilder,
        SerializerInterface $serializer,
        ?ClientInterface $client = null
    ) {
        parent::__construct($configurationBuilder->build(), $client);
        $this->serializer = $serializer;
    }

    /**
     * {@inheritdoc}
     */
    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        try {
            return parent::sendRequest($request);
        } catch (HttpException $exception) {
            $response = $exception->getResponse();
            $body = $this->serializer->deserialize($response->getBody(), ErrorResponseDto::class, JsonEncoder::FORMAT);
            $message = $body->extractMessage();

            if ($message) {
                (new PayPalError($message))->throwException($exception);
            }

            throw $exception;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function refundOrder(string $captureId, RefundRequestDto $payload): RefundResponseDto
    {
        $body = $this->serializer->serialize($payload, JsonEncoder::FORMAT, [
            AbstractObjectNormalizer::SKIP_NULL_VALUES => true
        ]);

        $response = $this->sendRequest(new Request('POST', "captures/$captureId/refund", [], $body));

        return $this->serializer->deserialize($response->getBody(), RefundResponseDto::class, JsonEncoder::FORMAT);
    }

    /**
     * {@inheritdoc}
     */
    public function captureAuthorization(string $authorizationId, ?CaptureRequestDto $payload = null): CaptureResponseDto
    {
        $body = $this->serializer->serialize($payload, JsonEncoder::FORMAT, [
            AbstractObjectNormalizer::SKIP_NULL_VALUES => true
        ]);

        $response = $this->sendRequest(new Request('POST', "authorizations/$authorizationId/capture", [], $body));

        return $this->serializer->deserialize($response->getBody(), CaptureResponseDto::class, JsonEncoder::FORMAT);
    }

    /**
     * {@inheritdoc}
     */
    public function voidAuthorization(string $authorizationId): VoidAuthorizationResponseDto
    {
        $response = $this->sendRequest(new Request('POST', "authorizations/$authorizationId/void"));

        return $this->serializer->deserialize($response->getBody(), VoidAuthorizationResponseDto::class, JsonEncoder::FORMAT);
    }

    /**
     * @inheritDoc
     */
    public function getAuthorization(string $authorizationId): GetAuthorizationResponseDto
    {
        $response = $this->sendRequest(new Request('GET', "authorizations/$authorizationId"));

        return $this->serializer->deserialize($response->getBody(), GetAuthorizationResponseDto::class, JsonEncoder::FORMAT);
    }

    /**
     * @inheritDoc
     */
    public function reauthorizeAuthorization(string $authorizationId, ?ReauthorizeRequestDto $requestDto = null): ReauthorizeResponseDto
    {
        $payload = $this->serializer->serialize($requestDto, JsonEncoder::FORMAT, [
            AbstractObjectNormalizer::SKIP_NULL_VALUES => true
        ]);

        $response = $this->sendRequest(new Request('POST', "authorizations/$authorizationId/reauthorize", [], $payload));

        return $this->serializer->deserialize($response->getBody(), ReauthorizeResponseDto::class, JsonEncoder::FORMAT);
    }
}

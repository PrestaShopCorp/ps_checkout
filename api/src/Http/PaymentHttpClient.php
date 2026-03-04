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
use PsCheckout\Api\Dto\PayPal\Payment\PaymentAuthorizationResponseDto;
use PsCheckout\Api\Dto\PayPal\Payment\ReauthorizeAuthorizationRequestDto;
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
            $body = json_decode($response->getBody(), true);
            $message = $this->extractMessage($body);

            if ($message) {
                (new PayPalError($message))->throwException($exception);
            }

            throw $exception;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function refundOrder(string $captureId, array $payload): ResponseInterface
    {
        $body = $this->generatePayloadString($payload);

        return $this->sendRequest(new Request('POST', "captures/$captureId/refund", [], $body));
    }

    /**
     * {@inheritdoc}
     */
    public function captureAuthorization(string $authorizationId, array $payload = []): ResponseInterface
    {
        $payloadString = !empty($payload) && json_encode($payload) ? json_encode($payload) : '{}';

        return $this->sendRequest(new Request('POST', "authorizations/$authorizationId/capture", [], $payloadString));
    }

    /**
     * {@inheritdoc}
     */
    public function voidAuthorization(string $authorizationId, array $payload = []): ResponseInterface
    {
        $body = $this->generatePayloadString($payload);

        return $this->sendRequest(new Request('POST', "authorizations/$authorizationId/void", [], $body));
    }

    /**
     * @inheritDoc
     */
    public function getAuthorization(string $authorizationId): PaymentAuthorizationResponseDto
    {
        $response = $this->sendRequest(new Request('GET', "authorizations/$authorizationId"));

        return $this->serializer->deserialize($response->getBody(), PaymentAuthorizationResponseDto::class, JsonEncoder::FORMAT);
    }

    /**
     * @inheritDoc
     */
    public function reauthorizeAuthorization(string $authorizationId, ?ReauthorizeAuthorizationRequestDto $requestDto = null): PaymentAuthorizationResponseDto
    {
        $payload = [];
        if ($requestDto) {
            $payload = $this->serializer->serialize($requestDto, JsonEncoder::FORMAT, [
                AbstractObjectNormalizer::SKIP_NULL_VALUES => true
            ]);
        }
        $response = $this->sendRequest(new Request('POST', "authorizations/$authorizationId/reauthorize", [], empty($payload) ? '{}' : $payload));

        return $this->serializer->deserialize($response->getBody(), PaymentAuthorizationResponseDto::class, JsonEncoder::FORMAT);
    }

    /**
     * @param array $body
     *
     * @return string
     */
    private function extractMessage(array $body): string
    {
        if (isset($body['details'][0]['issue']) && preg_match('/^[0-9A-Z_]+$/', $body['details'][0]['issue']) === 1) {
            return $body['details'][0]['issue'];
        }

        if (isset($body['error']) && preg_match('/^[0-9A-Z_]+$/', $body['error']) === 1) {
            return $body['error'];
        }

        if (isset($body['message']) && is_array($body['message'])) {
            return implode("\n", $body['message']);
        }

        if (isset($body['message']) && preg_match('/^[0-9A-Z_]+$/', $body['message']) === 1) {
            return $body['message'];
        }

        if (isset($body['name']) && preg_match('/^[0-9A-Z_]+$/', $body['name']) === 1) {
            return $body['name'];
        }

        return '';
    }

    /**
     * @param array<mixed> $payload
     * @return string
     */
    private function generatePayloadString(array $payload): string
    {
        $body = '{}';
        if (!empty($payload)) {
            $encoded = json_encode($payload);
            if ($encoded === false) {
                throw new \RuntimeException('Failed to encode payload to JSON');
            }
            $body = $encoded;
        }

        return $body;
    }
}

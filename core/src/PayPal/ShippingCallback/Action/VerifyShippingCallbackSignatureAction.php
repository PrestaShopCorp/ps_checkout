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

namespace PsCheckout\Core\PayPal\ShippingCallback\Action;

use PsCheckout\Core\PayPal\ShippingCallback\Exception\ShippingCallbackException;
use PsCheckout\Core\PayPal\ShippingCallback\Provider\ShippingCallbackCertProviderInterface;

class VerifyShippingCallbackSignatureAction implements VerifyShippingCallbackSignatureActionInterface
{
    const ALLOWED_CERT_HOSTS = [
        'api.paypal.com',
        'api.sandbox.paypal.com',
        'api-m.paypal.com',
        'api-m.sandbox.paypal.com',
    ];

    /**
     * @var ShippingCallbackCertProviderInterface
     */
    private $certProvider;

    public function __construct(ShippingCallbackCertProviderInterface $certProvider)
    {
        $this->certProvider = $certProvider;
    }

    /**
     * {@inheritDoc}
     */
    public function execute(string $rawBody, array $headers): void
    {
        if (!extension_loaded('openssl')) {
            throw new \RuntimeException('OpenSSL extension is required to verify PayPal callback signatures');
        }

        $transmissionId = $headers['Paypal-Transmission-Id'] ?? null;
        $transmissionTime = $headers['Paypal-Transmission-Time'] ?? null;
        $transmissionSig = $headers['Paypal-Transmission-Sig'] ?? null;
        $certUrl = $headers['Paypal-Cert-Url'] ?? null;
        $transmissionAlg = $headers['Paypal-Transmission-Alg'] ?? null;

        if (!$transmissionId || !$transmissionTime || !$transmissionSig || !$certUrl) {
            throw new ShippingCallbackException(
                ShippingCallbackException::INVALID_SIGNATURE,
                'Missing PayPal signature headers'
            );
        }

        if ((string) parse_url($certUrl, PHP_URL_SCHEME) !== 'https') {
            throw new ShippingCallbackException(
                ShippingCallbackException::INVALID_SIGNATURE,
                'PayPal cert URL must use HTTPS'
            );
        }

        $host = (string) parse_url($certUrl, PHP_URL_HOST);

        if (!in_array($host, self::ALLOWED_CERT_HOSTS, true)) {
            throw new ShippingCallbackException(
                ShippingCallbackException::INVALID_SIGNATURE,
                sprintf('Untrusted PayPal cert URL host: %s', $host)
            );
        }

        // CRC32 as unsigned decimal, matching PayPal's calculation
        $crc = sprintf('%u', crc32($rawBody));

        $message = sprintf('%s|%s|%s', $transmissionId, $transmissionTime, $crc);

        $certPem = $this->certProvider->getCert($certUrl);

        $publicKey = openssl_pkey_get_public($certPem);

        if ($publicKey === false) {
            throw new ShippingCallbackException(
                ShippingCallbackException::INVALID_SIGNATURE,
                'Failed to extract public key from PayPal cert'
            );
        }

        $signature = base64_decode($transmissionSig, true);

        if ($signature === false) {
            throw new ShippingCallbackException(
                ShippingCallbackException::INVALID_SIGNATURE,
                'Failed to decode PayPal transmission signature'
            );
        }

        $supportedAlgorithms = ['SHA256withRSA' => OPENSSL_ALGO_SHA256];

        if ($transmissionAlg !== null && !isset($supportedAlgorithms[$transmissionAlg])) {
            throw new ShippingCallbackException(
                ShippingCallbackException::INVALID_SIGNATURE,
                sprintf('Unsupported PayPal transmission algorithm: %s', $transmissionAlg)
            );
        }

        $algorithm = isset($supportedAlgorithms[$transmissionAlg]) ? $supportedAlgorithms[$transmissionAlg] : OPENSSL_ALGO_SHA256;

        $result = openssl_verify($message, $signature, $publicKey, $algorithm);

        if ($result !== 1) {
            throw new ShippingCallbackException(
                ShippingCallbackException::INVALID_SIGNATURE,
                sprintf('Invalid PayPal callback signature (message: %s)', $message)
            );
        }
    }
}

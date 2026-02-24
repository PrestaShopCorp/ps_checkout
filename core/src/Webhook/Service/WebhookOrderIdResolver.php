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

namespace PsCheckout\Core\Webhook\Service;

use PsCheckout\Core\PayPal\Order\Repository\PayPalOrderCaptureRepositoryInterface;

class WebhookOrderIdResolver
{
    /** @var PayPalOrderCaptureRepositoryInterface */
    private $captureRepository;

    public function __construct(PayPalOrderCaptureRepositoryInterface $captureRepository)
    {
        $this->captureRepository = $captureRepository;
    }

    /**
     * Resolves the PayPal order ID from a normalized Svix webhook body.
     *
     * @param array{
     *     resourceType: string,
     *     resource: array<string, mixed>,
     * } $bodyValues
     *
     * @return string|null
     */
    public function resolve(array $bodyValues): ?string
    {
        $resourceType = (string) ($bodyValues['resourceType'] ?? '');
        $resource = (array) ($bodyValues['resource'] ?? []);

        if (isset($resource['supplementary_data']['related_ids']['order_id'])) {
            return (string) $resource['supplementary_data']['related_ids']['order_id'];
        }

        switch ($resourceType) {
            case 'checkout-order':
                return isset($resource['id']) ? (string) $resource['id'] : null;

            case 'capture':
            case 'authorization':
                return $this->resolveFromCaptureResource($resource);

            case 'refund':
                return $this->resolveFromRefundResource($resource);

            default:
                return null;
        }
    }

    /**
     * @param array<string, mixed> $resource
     *
     * @return string|null
     */
    private function resolveFromCaptureResource(array $resource): ?string
    {
        // 1. rel:up link pointing to /v2/checkout/orders/{orderId}
        $orderId = $this->extractIdFromLinks((array) ($resource['links'] ?? []), 'checkout/orders');
        if ($orderId !== null) {
            return $orderId;
        }

        // 2. DB fallback: look up capture record by its own ID to retrieve the stored order ID
        if (!empty($resource['id'])) {
            $capture = $this->captureRepository->getById((string) $resource['id']);
            if ($capture !== null) {
                return $capture->getIdOrder();
            }
        }

        return null;
    }

    /**
     * @param array<string, mixed> $resource
     *
     * @return string|null
     */
    private function resolveFromRefundResource(array $resource): ?string
    {
        // rel:up link points to /v2/payments/captures/{captureId}; pivot through capture to get the order ID
        $captureId = $this->extractIdFromLinks((array) ($resource['links'] ?? []), 'payments/captures');
        if ($captureId === null) {
            return null;
        }

        $capture = $this->captureRepository->getById($captureId);

        return $capture !== null ? $capture->getIdOrder() : null;
    }

    /**
     * Finds the link with rel="up" whose href contains $pathSegment and returns the last path segment (the ID).
     *
     * @param array<int, array{href: string, rel: string}> $links
     * @param string $pathSegment
     *
     * @return string|null
     */
    private function extractIdFromLinks(array $links, string $pathSegment): ?string
    {
        foreach ($links as $link) {
            if (($link['rel'] ?? '') !== 'up') {
                continue;
            }

            $href = (string) ($link['href'] ?? '');
            if (strpos($href, $pathSegment) === false) {
                continue;
            }

            $id = basename((string) parse_url($href, PHP_URL_PATH));
            if ($id !== '') {
                return $id;
            }
        }

        return null;
    }
}

<?php

namespace PsCheckout\Core\PayPal\Payment\Authorization\Processor;

interface AuthorizationActionProcessorInterface
{
    /**
     * @param string $action
     * @param string|null $orderId
     *
     * @return array{
     *     status: bool,
     *     httpCode?: int,
     *     error?: array{message: string, code: int}
     * }
     */
    public function process(string $action, ?string $orderId): array;
}
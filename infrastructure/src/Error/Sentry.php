<?php

namespace PsCheckout\Infrastructure\Error;

use PsCheckout\Infrastructure\Environment\EnvInterface;

final class Sentry
{
    /**
     * @var string
     */
    private $dsn;

    public function __construct(
        EnvInterface $moduleEnv
    )
    {
        $dsn = $moduleEnv->getEnv('SENTRY_DSN');
        if (is_string($dsn) && !empty($dsn)) {
            $this->dsn = $dsn;
        } else {
            $this->dsn = '';
        }
    }

    public function init(): void
    {
        \Sentry\init([
            'dsn' => $this->dsn,
        ]);
    }
}
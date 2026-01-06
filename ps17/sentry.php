<?php

$dsn = getenv('SENTRY_DSN');
if (!is_string($dsn) || empty($dsn)) {
    return;
}

\Sentry\init([
    'dsn' => $dsn,
    'send_default_pii' => true,
]);

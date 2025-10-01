<?php
$rootDirectory = __DIR__ . '/../../../../../../';
$projectDir = __DIR__ . '/../../../../';

if (!getenv('IS_CI')) {
    define('_PS_IN_TEST_', true);
    define('_PS_MODE_DEV_', true);
    define('_PS_ADMIN_DIR_', __DIR__ . '/admin');
    define('_PS_FRONT_', true);  // Ensure front is enabled in the test environment
}

require_once $projectDir . 'vendor/autoload.php';
require_once $rootDirectory . 'config/config.inc.php';

if (file_exists($rootDirectory . 'vendor/autoload.php')) {
    require_once $rootDirectory . 'vendor/autoload.php';
}

if (file_exists($rootDirectory . 'autoload.php')) {
    require_once $rootDirectory . 'autoload.php';
}

if (class_exists(AppKernel::class)) {
    $kernel = new AppKernel('dev', _PS_MODE_DEV_);
    $kernel->boot();
}

// any actions to apply before any given tests can be done here

<?php

$rootDirectory = getenv('_PS_ROOT_DIR_') ?: __DIR__ . '/../../../../';
$projectDir = __DIR__ . '/../../';
require_once $rootDirectory . 'config/config.inc.php';
require_once $projectDir . 'vendor/autoload.php';

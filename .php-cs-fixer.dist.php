<?php

require_once './vendor/prestashop/php-dev-tools/src/CsFixer/Config.php';

$config = new PrestaShop\CodingStandards\CsFixer\Config();

/** @var \Symfony\Component\Finder\Finder $finder */
$finder = $config->setUsingCache(true)->getFinder();
$finder->in(__DIR__)->exclude('vendor');

return $config;

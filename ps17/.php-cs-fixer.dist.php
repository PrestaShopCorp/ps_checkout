<?php

$config = new PrestaShop\CodingStandards\CsFixer\Config();

/** @var \Symfony\Component\Finder\Finder $finder */
$finder = $config->setUsingCache(true)->getFinder();

// Include specific directories inside vendor
$finder->in([
    __DIR__ . '/vendor/invertus/core',
    __DIR__ . '/vendor/invertus/infrastructure',
    __DIR__ . '/vendor/invertus/presentation',
]);

$finder->notName('index.php');

// Exclude everything else inside vendor (excluding invertus directory)
$finder->notPath('/vendor\/(?!invertus)/');

return $config;
<?php

$rootDirectory = __DIR__ . '/../../../../../';
$projectDir = __DIR__ . '/../../../';

require_once $projectDir . 'vendor/autoload.php';
require_once $rootDirectory . 'config/config.inc.php';

if (file_exists($rootDirectory . 'vendor/autoload.php')) {
    require_once $rootDirectory . 'vendor/autoload.php';
}

if (file_exists($rootDirectory . 'autoload.php')) {
    require_once $rootDirectory . 'autoload.php';
}

// Source database details
$sourceHost = '172.26.0.2';
$sourceUser = _DB_USER_;
$sourcePassword = _DB_PASSWD_;
$sourceDatabase = _DB_NAME_;

// Target (new) database details
$targetDatabase = 'test_' . $sourceDatabase;

$createDatabaseCommand = "mysql -h$sourceHost -u$sourceUser -p$sourcePassword -e 'DROP DATABASE IF EXISTS `" . $targetDatabase . '`; CREATE DATABASE `' . $targetDatabase . "`'";
$importCommand = "mysqldump -h$sourceHost -u" . $sourceUser . " -p'" . $sourcePassword . "' " . $sourceDatabase . " | mysql -h$sourceHost -u " . $sourceUser . " --password='" . $sourcePassword . "' " . $targetDatabase;

echo 'Step 1: Creating New Database' . PHP_EOL;
echo shell_exec($createDatabaseCommand) . PHP_EOL;

echo 'Step 2: Importing into New Database' . PHP_EOL;
echo shell_exec($importCommand) . PHP_EOL;

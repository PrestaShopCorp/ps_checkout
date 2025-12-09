<?php

$rootDirectory = __DIR__ . '/../../../';
$projectDir = __DIR__ . '/../';

require_once $projectDir . 'vendor/autoload.php';
require_once $rootDirectory . 'config/config.inc.php';

if (file_exists($rootDirectory . 'vendor/autoload.php')) {
    require_once $rootDirectory . 'vendor/autoload.php';
}

if (file_exists($rootDirectory . 'autoload.php')) {
    require_once $rootDirectory . 'autoload.php';
}

$sourceHost = 'mysql';
$sourceUser = defined('_DB_USER_') ? _DB_USER_ : 'root';
$sourcePassword = defined('_DB_PASSWD_') ? _DB_PASSWD_ : 'prestashop';
$sourceDatabase = defined('_DB_NAME_') ? _DB_NAME_ : 'prestashop';

// Target (new) database details
$targetDatabase = 'test_' . $sourceDatabase;

$createDatabaseCommand = "mysql -h$sourceHost -u$sourceUser -p$sourcePassword -e 'DROP DATABASE IF EXISTS `" . $targetDatabase . '`; CREATE DATABASE `' . $targetDatabase . "`'";
$importCommand = "mysqldump -h$sourceHost -u" . $sourceUser . " -p'" . $sourcePassword . "' " . $sourceDatabase . " | mysql -h$sourceHost -u " . $sourceUser . " --password='" . $sourcePassword . "' " . $targetDatabase;

echo 'Step 1: Creating New Database' . PHP_EOL;
echo shell_exec($createDatabaseCommand) . PHP_EOL;

echo 'Step 2: Importing into New Database' . PHP_EOL;
echo shell_exec($importCommand) . PHP_EOL;

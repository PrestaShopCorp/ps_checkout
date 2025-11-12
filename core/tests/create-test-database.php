<?php

$rootDirectory = __DIR__ . '/../../../../../../';
$projectDir = __DIR__ . '/../../../../';

$sourceHost = defined('_DB_SERVER_') ? _DB_SERVER_ : 'mysql';
$sourceUser = defined('_DB_USER_') ? _DB_USER_ : 'root';
$sourcePassword = defined('_DB_PASSWD_') ? _DB_PASSWD_ : 'prestashop';
$sourceDatabase = defined('_DB_NAME_') ? _DB_NAME_ : 'prestashop';

// Target (new) database details
$targetDatabase = 'test_' . $sourceDatabase;

$createDatabaseCommand = "mysql -h$sourceHost -u$sourceUser -p$sourcePassword -e 'DROP DATABASE IF EXISTS `" . $targetDatabase . '`; CREATE DATABASE `' . $targetDatabase . "`'";
$importCommand = "mysqldump -h$sourceHost -u" . $sourceUser . " -p'" . $sourcePassword . "' " . $sourceDatabase . " | mysql -h$sourceHost -u " . $sourceUser . " --password='" . $sourcePassword . "' " . $targetDatabase;

//echo 'Step 1: Creating New Database' . PHP_EOL;
//echo shell_exec($createDatabaseCommand) . PHP_EOL;
//
//echo 'Step 2: Importing into New Database' . PHP_EOL;
//echo shell_exec($importCommand) . PHP_EOL;

shell_exec($createDatabaseCommand);
shell_exec($importCommand);
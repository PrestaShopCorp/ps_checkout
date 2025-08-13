<?php

$config = new PhpCsFixer\Config();

$finder = $config->getFinder();
$finder->in([__DIR__]); // This includes everything in the current directory (the root directory)
$finder->notName('index.php');

return $config->setRules([
    '@PSR2' => true,
    'class_attributes_separation' => true,
    'blank_line_before_statement' => [
        'statements' => ['return', 'throw', 'continue', 'break', 'declare', 'exit'],
    ],
]);
<?php

declare(strict_types=1);

use PhpCsFixer\Config;
use PhpCsFixer\Finder;
use PhpCsFixer\Runner\Parallel\ParallelConfigFactory;

return (new Config())
    ->setParallelConfig(ParallelConfigFactory::detect())
    ->setRiskyAllowed(false)
    ->setRules([
        '@PSR2' => true,
        'class_attributes_separation' => true,
        'blank_line_before_statement' => [
            'statements' => ['return', 'throw', 'continue', 'break', 'declare', 'exit'],
        ],
    ])
    // 💡 by default, Fixer looks for `*.php` files excluding `./vendor/` - here, you can groom this config
    ->setFinder(
        (new Finder())
            // 💡 root folder to check
            ->in(['api', 'core', 'infrastructure', 'presentation', 'utility'])
            ->notName(['index.php'])
    )
;

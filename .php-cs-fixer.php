<?php

declare(strict_types=1);

use PhpCsFixer\Config;
use PhpCsFixer\Finder;
use PhpCsFixer\Runner\Parallel\ParallelConfigFactory;

$header = <<<'EOF'
Copyright since 2007 PrestaShop SA and Contributors
PrestaShop is an International Registered Trademark & Property of PrestaShop SA

NOTICE OF LICENSE

This source file is subject to the Academic Free License version 3.0
that is bundled with this package in the file LICENSE.md.
It is also available through the world-wide-web at this URL:
https://opensource.org/licenses/AFL-3.0
If you did not receive a copy of the license and are unable to
obtain it through the world-wide-web, please send an email
to license@prestashop.com so we can send you a copy immediately.

@author    PrestaShop SA and Contributors <contact@prestashop.com>
@copyright Since 2007 PrestaShop SA and Contributors
@license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
EOF;

return (new Config())
    ->setParallelConfig(ParallelConfigFactory::detect())
    ->setRiskyAllowed(false)
    ->setRules([
        '@PSR2' => true,
        'no_unused_imports' => true,
        'class_attributes_separation' => true,
        'blank_line_before_statement' => [
            'statements' => ['return', 'throw', 'continue', 'break', 'declare', 'exit'],
        ],
        'header_comment' => [
            'header' => $header,
            'comment_type' => 'PHPDoc',
            'separate' => 'bottom',
            'location' => 'after_open'
]
    ])
    // 💡 by default, Fixer looks for `*.php` files excluding `./vendor/` - here, you can groom this config
    ->setFinder(
        (new Finder())
            // 💡 root folder to check
            ->in(['api', 'core', 'infrastructure', 'presentation', 'utility'])
            ->notName(['index.php'])
    )
;

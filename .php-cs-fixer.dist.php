<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__.'/{src}');

$header = <<<'HEADER'
This file is part of aakb/kunstdatabasen.
(c) 2020 ITK Development
This source file is subject to the MIT license.
HEADER;

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules(
        [
            '@Symfony' => true,
            '@Symfony:risky' => true,
            'array_syntax' => ['syntax' => 'short'],
            'header_comment' => ['header' => $header],
            'list_syntax' => ['syntax' => 'short'],
            'no_superfluous_phpdoc_tags' => false,
            'method_argument_space' => ['on_multiline' => 'ensure_fully_multiline'],
            'strict_comparison' => true,
        ]
    )
    ->setFinder($finder);

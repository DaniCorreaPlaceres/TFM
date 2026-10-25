<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->exclude(['var', 'vendor']);

return (new PhpCsFixer\Config())
    ->setRules([
        '@Symfony' => true,
        'line_ending' => false,
    ])
    ->setFinder($finder);

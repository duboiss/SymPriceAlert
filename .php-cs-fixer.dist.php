<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->exclude('config')
    ->exclude('public')
    ->exclude('var')
    ->notPath('src/Kernel.php')
;

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@PhpCsFixer' => true,
        '@PhpCsFixer:risky' => true,
        '@PHP80Migration' => true,
        '@PHP80Migration:risky' => true,
        '@DoctrineAnnotation' => true,
        'concat_space' => ['spacing' => 'one'],
        'declare_strict_types' => true,
        'mb_str_functions' => true,
        'native_function_invocation' => [],
        'ordered_class_elements' => ['order' => ['use_trait']],
        'phpdoc_types_order' => ['null_adjustment' => 'always_last', 'sort_algorithm' => 'none'],
        'php_unit_internal_class' => false,
        'php_unit_test_class_requires_covers' => false,
        'yoda_style' => ['equal' => false, 'identical' => false, 'less_and_greater' => false],
    ])
    ->setFinder($finder)
    ->setCacheFile(__DIR__.'/var/.php-cs-fixer.cache')
;

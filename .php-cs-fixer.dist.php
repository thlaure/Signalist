<?php

declare(strict_types=1);

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->exclude('var')
    ->exclude('vendor')
    ->exclude('docker')
    ->exclude('frontend')
;

return (new PhpCsFixer\Config())
    ->setRules([
        '@PSR12' => true,
        '@Symfony' => true,
        '@Symfony:risky' => true,
        'declare_strict_types' => true,
        'strict_param' => true,
        'array_syntax' => ['syntax' => 'short'],
        'ordered_imports' => ['sort_algorithm' => 'alpha'],
        'no_unused_imports' => true,
        'trailing_comma_in_multiline' => ['elements' => ['arrays', 'arguments', 'parameters']],
        'phpdoc_order' => true,
        'phpdoc_separation' => true,
        'phpdoc_align' => ['align' => 'left'],
        'concat_space' => ['spacing' => 'one'],
        'cast_spaces' => ['space' => 'single'],
        'binary_operator_spaces' => [
            'default' => 'single_space',
            'operators' => [
                '=>' => 'single_space',
            ],
        ],
        'class_attributes_separation' => [
            'elements' => [
                'const' => 'one',
                'method' => 'one',
                'property' => 'one',
            ],
        ],
        'single_line_throw' => false,
        'yoda_style' => false,
        'blank_line_before_statement' => [
            'statements' => [
                'return',
                'throw',
                'try',
                'if',
            ],
        ],
        'global_namespace_import' => [
            'import_classes' => true,
            'import_constants' => true,
            'import_functions' => true,
        ],
        'native_function_invocation' => [
            'include' => ['@compiler_optimized'],
            'scope' => 'namespaced',
        ],
        'nullable_type_declaration_for_default_null_value' => true,
        'no_superfluous_phpdoc_tags' => [
            'allow_mixed' => true,
            'remove_inheritdoc' => true,
        ],
        'final_class' => true,
        'self_accessor' => true,
        'php_unit_method_casing' => ['case' => 'camel_case'],
        'php_unit_test_annotation' => ['style' => 'prefix'],
        'php_unit_strict' => true,
    ])
    ->setRiskyAllowed(true)
    ->setFinder($finder)
    ->setCacheFile('var/.php-cs-fixer.cache')
;

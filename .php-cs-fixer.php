<?php

declare(strict_types=1);
$rules = [
    '@PSR2' => true,
    '@PhpCsFixer' => true,
    'single_trait_insert_per_statement' => false,
    'unary_operator_spaces' => false,
    'array_syntax' => ['syntax' => 'short'],
    'multiline_whitespace_before_semicolons' => true,
    'echo_tag_syntax' => ['format' => 'long'],
    'no_unused_imports' => true,
    'no_useless_else' => true,
    'no_superfluous_phpdoc_tags' => false,
    'ordered_imports' => [
        'sort_algorithm' => 'length',
    ],
    'phpdoc_add_missing_param_annotation' => true,
    'phpdoc_indent' => true,
    'phpdoc_no_package' => true,
    'phpdoc_order' => true,
    'phpdoc_single_line_var_spacing' => true,
    'phpdoc_trim' => true,
    'phpdoc_var_without_name' => true,
    'phpdoc_to_comment' => true,
    'single_quote' => true,
    'ternary_operator_spaces' => true,
    'trailing_comma_in_multiline' => ['elements' => ['arrays']],
    'trim_array_spaces' => true,
    'array_indentation' => true,
    'not_operator_with_successor_space' => false,
    'no_whitespace_in_blank_line' => false,
    'concat_space' => [
        'spacing' => 'one',
    ],
    'phpdoc_no_empty_return' => false,
    'phpdoc_separation' => false,
    'declare_strict_types' => true,
    'php_unit_test_class_requires_covers' => false,
    'php_unit_method_casing' => ['case' => 'snake_case'],
    'global_namespace_import' => true,
    'yoda_style' => false,
];

$excludes = [
    'vendor',
    'storage',
    'node_modules',
];

return (new PhpCsFixer\Config())
    ->setRules($rules)
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->in(__DIR__)
            ->exclude($excludes)
            ->notName('README.md')
            ->notName('*.xml')
            ->notName('*.yml')
            ->notName('_ide_helper.php')
    );

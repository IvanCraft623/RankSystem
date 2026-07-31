<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__ . '/src');

return (new PhpCsFixer\Config)
    ->setRiskyAllowed(true)
    ->setRules([
        'align_multiline_comment' => [
            'comment_type' => 'phpdocs_only'
        ],
        'array_indentation' => true,
        'array_syntax' => [
            'syntax' => 'short'
        ],
        'binary_operator_spaces' => [
            'default' => 'single_space'
        ],
        'blank_line_after_namespace' => true,
        'blank_line_after_opening_tag' => true,
        'blank_line_before_statement' => [
            'statements' => [
                'declare'
            ]
        ],
        'cast_spaces' => [
            'space' => 'single'
        ],
        'concat_space' => [
            'spacing' => 'one'
        ],
        'declare_strict_types' => true,
        'elseif' => true,
        'fully_qualified_strict_types' => true,
        'global_namespace_import' => [
            'import_constants' => true,
            'import_functions' => true,
            'import_classes' => null,
        ],
        'header_comment' => [
            'comment_type' => 'comment',
            'header' => <<<BODY
  ____             _     ____
 |  _ \ __ _ _ __ | | __/ ___| _   _ ___| |_ ___ _ __ ___  
 | |_) / _` | '_ \| |/ /\___ \| | | / __| __/ _ \ '_ ` _ \
 |  _ < (_| | | | |   <  ___) | |_| \__ \ ||  __/ | | | | |
 |_| \_\__,_|_| |_|_|\_\|____/ \__, |___/\__\___|_| |_| |_|
                               |___/

An amazing rank and permissions manager for PocketMine-MP.

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

    http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.

@author IvanCraft623
BODY,
            'location' => 'after_open'
        ],
        'indentation_type' => true,
        'logical_operators' => true,
        'native_constant_invocation' => [
            'scope' => 'namespaced'
        ],
        'native_function_invocation' => [
            'scope' => 'namespaced',
            'include' => ['@all'],
        ],
        'new_with_braces' => [
            'named_class' => true,
            'anonymous_class' => false,
        ],
        'no_closing_tag' => true,
        'no_empty_phpdoc' => true,
        'no_extra_blank_lines' => true,
        'no_superfluous_phpdoc_tags' => [
            'allow_mixed' => true,
        ],
        'no_trailing_whitespace' => true,
        'no_trailing_whitespace_in_comment' => true,
        'no_whitespace_in_blank_line' => true,
        'no_unused_imports' => true,
        'ordered_imports' => [
            'imports_order' => [
                'class',
                'function',
                'const',
            ],
            'sort_algorithm' => 'alpha'
        ],
        'phpdoc_align' => [
            'align' => 'vertical',
            'tags' => [
                'param',
            ]
        ],
        'phpdoc_line_span' => [
            'property' => 'single',
            'method' => null,
            'const' => null
        ],
        'phpdoc_trim' => true,
        'phpdoc_trim_consecutive_blank_line_separation' => true,
        'return_type_declaration' => [
            'space_before' => 'one'
        ],
        'single_blank_line_at_eof' => true,
        'single_import_per_statement' => true,
        'strict_param' => true,
        'unary_operator_spaces' => true,
    ])
    ->setFinder($finder)
    ->setIndent("\t")
    ->setLineEnding("\n");

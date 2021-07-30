<?php

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

// https://mlocati.github.io/php-cs-fixer-configurator/#version:3.0
// php-cs-fixer v3.0.0
// 升级至3.0 https://github.com/FriendsOfPHP/PHP-CS-Fixer/blob/3.0/UPGRADE-v3.md#bc-breaks-various
// 自检配置文件命令 PHP_CS_FIXER_FUTURE_MODE=1 php-cs-fixer fix

$rules = [
    '@PSR2' => true,
    'psr_autoloading' => true,
    'strict_comparison' => true,
    'strict_param' => true,
    // 每个属性和方法都必须指定作用域是 public、protected 还是 private，abstract 和 final 必须位于作用域关键字之前，static 必须位于作用域之后
    'visibility_required' => [
        'elements' => ['property', 'method', 'const']
    ],
    // 'visibility_required' => 'elements',
    // 逻辑运算符 (!) 后必须紧跟一个空格。
    'not_operator_with_successor_space' => false,
    // 用相应的 mb 函数替换多字节不安全函数
    // 'mb_str_functions' => true,
    // 数组的每个元素必须缩进一次
    'array_indentation' => true,
    // 多行DocComments的每一行都必须带有星号[PSR-5]，并且必须与第一行对齐
    'align_multiline_comment' => true,
    'cast_spaces' => [
        // 类型强制转换和变量之间不能有单个空格
        // 变量和修饰符之间的间距
        'space' => 'single' // or none
    ],
    // PHP代码必须仅使用不带BOM的UTF-8（删除BOM）
    'encoding' => true,
    // 用替换is_null($var)表达式null === $var
    // 'is_null' => true,
    // 是否使用long或short数组语法
    'array_syntax' => ['syntax' => 'short'],
    // \?\>仅包含PHP的文件必须省略结束标记
    'no_closing_tag' => true,
    // 当多个 unset 使用的时候，合并处理
    'combine_consecutive_unsets' => true,
    // 类，特征和接口元素必须用空白行分隔（Class、trait、interface elements must be separated with one blank line.）
    'class_attributes_separation' => [
        'elements' => ['const' => 'none', 'method' => 'none', 'property' => 'none'] // none or one
    ],
    'multiline_whitespace_before_semicolons' => true,
    // 自增自减
    // 'standardize_increment' => true,
    // 简单字符串应该使用单引号代替双引号
    'single_quote' => true,
    'doctrine_annotation_spaces' => [
        'after_argument_assignments' => true,
        'before_argument_assignments' => true,
        'before_array_assignments_equals' => true,
        'before_array_assignments_colon' => true,
        'after_array_assignments_colon' => true,
        'after_array_assignments_equals' => true,
        'around_commas' => true,
        'around_parentheses' => true,
        // "ignored_tags" => []
    ],
    // 删除冒号和案例值之间的多余空格
    'switch_case_space' => true,
    // 标准化三元运算符周围的空间
    'ternary_operator_spaces' => true,
    // 必须要用空行分割类属性
    'class_attributes_separation' => [
        'elements' => ['const' => 'none', 'method' => 'one', 'property' => 'none']
    ],
    // 二元运算符应按配置用空格包围。
    'binary_operator_spaces' => [
        'operators' => [
            // 'align',
            // 'align_single_space',
            // 'align_single_space_minimal',
            // 'single_space',
            // 'no_space',
            // null,
            '=' => 'single_space', '*' => 'single_space', '/' => 'single_space', '%' => 'single_space', '<' => 'single_space', '>' => 'single_space', '|' => 'single_space', '^' => 'single_space', '+' => 'single_space', '-' => 'single_space', '&' => 'single_space', '&=' => 'single_space', '&&' => 'single_space', '||' => 'single_space', '.=' => 'single_space', '/=' => 'single_space', '=>' => 'single_space', '==' => 'single_space', '>=' => 'single_space', '===' => 'single_space', '!=' => 'single_space', '<>' => 'single_space', '!==' => 'single_space', '<=' => 'single_space', 'and' => 'single_space', 'or' => 'single_space', 'xor' => 'single_space', '-=' => 'single_space', '%=' => 'single_space', '*=' => 'single_space', '|=' => 'single_space', '+=' => 'single_space', '<<' => 'single_space', '<<=' => 'single_space', '>>' => 'single_space', '>>=' => 'single_space', '^=' => 'single_space', '**' => 'single_space', '**=' => 'single_space', '<=>' => 'single_space', '??' => 'single_space', '??=' => 'single_space',
        ]
    ],
    // 'blank_line_after_opening_tag' => true,
    // 'blank_line_before_return' => true,
    // 所有语句块都必须包含在花括号内，且位置以及缩进是符合标准的
    'braces' => [
        // 是否应允许使用单行 lambda（匿名函数） 表示法
        'allow_single_line_closure' => true,
        // 是否将花括号放在匿名构造（匿名类和lambda（匿名函数）函数）之后的下一行或同一行上
        'position_after_anonymous_constructs' => 'same', // 'next'
        // 花括号是否应放置在控制结构之后的 下一条 或 同一条 线上
        'position_after_control_structures' => 'next', // 'same'
        // 是否将花括号放在经典构造（非匿名类(non-anonymous)、接口类(interfaces)、特征(traits)、函数方法(methods) 和 非lambda（匿名函数）函数）之后的下一行或同一行上
        'position_after_functions_and_oop_constructs' => 'next' // 'same'
    ],
    // 类，特征或接口定义的关键字周围的空格应为一个空格
    'class_definition' => [
        'single_line' => true,
        'single_item_single_line' => true,
        'multi_line_extends_each_single_line' => true
    ],
    // 点连接符左右两边有一个的空格(.拼接必须有空格分割)
    'concat_space' => ['spacing' => 'one'],
    'declare_equal_normalize' => true,
    // 修正函数参数和类型提示之间的缺失的空格问题
    'function_typehint_space' => true,
    'single_line_comment_style' => true,
    'include' => true,
    // 类型强制小写
    'lowercase_cast' => true,
    // 常量为小写
    // 'lowercase_constants' => true,
    // 'native_function_casing' => true,
    // 实例化类时后面都应该带上括号
    'new_with_braces' => true,
    // 类开始标签后不应该有空白行
    'no_blank_lines_after_class_opening' => true,
    'no_blank_lines_after_phpdoc' => true,
    'no_empty_comment' => true,
    // 删除多余的分号
    'no_empty_statement' => true,
    // 删除代码段之间多余的空白行
    'no_extra_blank_lines' => [
        'tokens' => ['break', 'case', 'continue', 'curly_brace_block', 'default', 'extra', 'parenthesis_brace_block', 'return', 'square_brace_block', 'switch', 'throw', 'use', 'use_trait']
    ],
    // 删除 use 前的斜杠
    'no_leading_import_slash' => true,
    // 命名空间前面不应该有空格
    'no_leading_namespace_whitespace' => true,
    // 命名空间声明前应该正好有一个空行, 只能有一个
    'single_blank_line_before_namespace' => true,
    // 命名空间声明前不应出现空行, 只能有一个
    // 'no_blank_lines_before_namespace' => true,
    // 命名空间的声明后必须有一个空白行
    'blank_line_after_namespace' => true,
    'no_mixed_echo_print' => ['use' => 'echo'], // 'print'
    'no_multiline_whitespace_around_double_arrow' => true,
    // 'no_short_bool_cast' => true,
    // 'no_singleline_whitespace_before_semicolons' => true,
    // 偏移括号周围一定不能有空格
    'no_spaces_around_offset' => [
        'positions' => [
            'inside',
            'outside',
        ]
    ],
    // 删除 list 语句中多余的逗号
    // 'no_trailing_comma_in_list_call' => true,
    // PHP 单行数组最后一个元素后面不应该有空格
    'no_trailing_comma_in_singleline_array' => true,
    // 'no_unneeded_control_parentheses' => true,
    // 删除未使用的 use 引入
    'no_unused_imports' => true,
    // 排序 use 导入
    'ordered_imports' => [
        'imports_order' => ['class', 'const', 'function'],
        'sort_algorithm' => 'length' // 'alpha', 'length', 'none',
    ],
    // 排序指定元素
    'ordered_class_elements' => [
        // 'order' => [
        //     'use_trait',
        //     'public',
        //     'protected',
        //     'private',
        //     'constant',
        //     'constant_public',
        //     'constant_protected',
        //     'constant_private',
        //     'property',
        //     'property_static',
        //     'property_public',
        //     'property_protected',
        //     'property_private',
        //     'property_public_static',
        //     'property_protected_static',
        //     'property_private_static',
        //     'construct',
        //     'destruct',
        //     'magic',
        //     'phpunit',
        //     // 方法函数禁止排序
        //     'method',
        //     'method_static',
        //     'method_public',
        //     'method_private',
        //     'method_protected',
        //     'method_public_static',
        //     'method_private_static',
        //     'method_protected_static',
        // ],
        // 'sort_algorithm' => 'alpha' /* none */
    ],
    // 每个 use 声明独立一行，且 use 语句块之后要有一个空白行
    'single_line_after_imports' => true,
    // 在数组声明中，每个逗号前不能有空格。
    'no_whitespace_before_comma_in_array' => true,
    // 删除空行中多余的空格
    'no_whitespace_in_blank_line' => true,
    // 删除非空白行末尾的空白
    'no_trailing_whitespace' => true,
    // 数组应始终使用方括号
    'normalize_index_brace' => true,
    // -> 两端不应有空格
    // 'object_operator_without_whitespace' => true,
    // 'php_unit_fqcn_annotation' => true,
    // 不应有空的PHPDoc块
    'no_empty_phpdoc' => true,
    'phpdoc_align' => true,
    // 'phpdoc_annotation_without_dot' => true,
    // phpdoc 应该保持缩进
    'phpdoc_indent' => true,
    // 'phpdoc_inline_tag' => true,
    // 'phpdoc_no_access' => true,
    // 'phpdoc_no_alias_tag' => true,
    // 'phpdoc_no_empty_return' => true,
    // 'phpdoc_no_package' => true,
    // 'phpdoc_no_useless_inheritdoc' => true,
    // 'phpdoc_return_self_reference' => true,
    // 'phpdoc_scalar' => true,
    // 'phpdoc_separation' => true,
    // 'phpdoc_single_line_var_spacing' => true,
    // 'phpdoc_summary' => true,
    // 'phpdoc_to_comment' => true,
    // 'phpdoc_trim' => true,
    // 'phpdoc_types' => true,
    // 'phpdoc_var_without_name' => true,
    // 'pre_increment' => true,
    // 'return_type_declaration' => true,
    // 在当前类中使用self代替类名
    'self_accessor' => true,
    // 'short_scalar_cast' => true,
    // 'single_class_element_per_statement' => true,
    // 'space_after_semicolon' => true,
    // 使用 <> 代替 !=
    // 'standardize_not_equals' => true,
    // 'trailing_comma_in_multiline_array' => true,
    // 数组需要格式化成和函数/方法参数类似，上下没有空白行
    'trim_array_spaces' => true,
    // 一元运算符和运算数需要相邻
    'unary_operator_spaces' => true,
    'whitespace_after_comma_in_array' => true
];

$finder = Finder::create()
    ->in(
        __DIR__
        // [__DIR__ . '/app',__DIR__ . '/config',__DIR__ . '/database',__DIR__ . '/resources',__DIR__ . '/routes',__DIR__ . '/tests']
    )
    ->name('*.php')
    // ->notName('*.blade.php')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true);

// Config::create() 已在 2.17 版中弃用并在 3.0 中删除

return (new Config())
    ->setRiskyAllowed(true)
    ->setUsingCache(false)
    ->setIndent("\t")
    ->setFinder($finder)
    ->setRules($rules)
    ->setLineEnding("\n");

<?php
/**
 * PHAN configuration
 *
 * @package skaut-google-drive-gallery
 */

return array(
	// Covered by PHPCS.
	'backward_compatibility_checks'             => false,
	'constant_variable_detection'               => true,
	'directory_list'                            => array(
		'src',
		'tests',
		'.phan',
		'dist/vendor',
		'vendor/skaut/wordpress-stubs/stubs',
	),
	'exclude_analysis_directory_list'           => array(
		'dist/vendor/',
		'vendor/skaut/wordpress-stubs/stubs',
	),
	'file_list'                                 => array(
		'scoper.inc.php',
	),
	'minimum_target_php_version'                => '5.6',
	'plugins'                                   => array(
		'AlwaysReturnPlugin',
		'DollarDollarPlugin',
		'DuplicateArrayKeyPlugin',
		'DuplicateExpressionPlugin',
		'EmptyStatementListPlugin',
		'InvalidVariableIssetPlugin',
		'NoAssertPlugin',
		'NonBoolBranchPlugin',
		'NonBoolInLogicalArithPlugin',
		'PossiblyStaticMethodPlugin',
		'PreferNamespaceUsePlugin',
		'PregRegexCheckerPlugin',
		'StrictComparisonPlugin',
		'SuspiciousParamOrderPlugin',
		'UnreachableCodePlugin',
		'UnusedSuppressionPlugin',
		'UseReturnValuePlugin',
	),
	'redundant_condition_detection'             => true,
	'strict_method_checking'                    => true,
	'strict_object_checking'                    => true,
	'strict_property_checking'                  => true,
	'strict_return_checking'                    => true,
	'suppress_issue_types'                      => array(
		'PhanPluginDuplicateConditionalNullCoalescing',
		'PhanPluginMixedKeyNoKey',
	),
	'target_php_version'                        => '8.1',
	'unused_variable_detection'                 => true,
	'warn_about_redundant_use_namespaced_class' => true,
	'warn_about_undocumented_throw_statements'  => true,
);

<?php

return [
	'target_php_version'              => '7.3',
	'backward_compatibility_checks'   => false,
	'analyze_signature_compatibility' => true,
	'minimum_severity'                => 0,
	'directory_list'                  => [
		'plugin',
		'.phan/stubs',
	],
	'exclude_analysis_directory_list' => [
		'plugin/bundled/vendor/',
	],
	'plugins'                         => [
		'AlwaysReturnPlugin',
		'UnreachableCodePlugin',
		'DollarDollarPlugin',
		'DuplicateArrayKeyPlugin',
		'PregRegexCheckerPlugin',
		'PrintfCheckerPlugin',
	],
];

/** @type {import('stylelint').Config} */
export default {
	extends: [
		'stylelint-config-standard',
		'@wordpress/stylelint-config/stylistic',
	],
	plugins: ['stylelint-no-unsupported-browser-features'],
	rules: {
		'color-function-notation': 'legacy',
		'plugin/no-unsupported-browser-features': [
			true,
			{
				severity: 'warning',
				ignore: 'css-sel2',
			},
		],
	},
};

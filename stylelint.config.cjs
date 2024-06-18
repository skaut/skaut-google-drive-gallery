/* eslint-env node */

/** @type {import('stylelint').Config} */
module.exports = {
	extends: '@wordpress/stylelint-config',
	plugins: ['stylelint-no-unsupported-browser-features'],
	rules: {
		'plugin/no-unsupported-browser-features': [
			true,
			{
				severity: 'warning',
				ignore: 'css-sel2',
			},
		],
	},
};

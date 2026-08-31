/** @type {import('stylelint').Config} */
export default {
	extends: [
		'stylelint-config-standard',
		'@wordpress/stylelint-config/stylistic',
	],
	plugins: ['stylelint-no-unsupported-browser-features'],
	rules: {
		'color-function-notation': 'legacy',
		// The `inset` shorthand needs Chrome 87 / Safari 14.1, above our floor, so
		// the longhands are deliberate.
		'declaration-block-no-redundant-longhand-properties': [
			true,
			{
				ignoreShorthands: ['inset'],
			},
		],
		'plugin/no-unsupported-browser-features': [
			true,
			{
				// Caniuse flags css-overflow as partial over `overflow: clip` and
				// two-value syntax. Plain `overflow-y: auto` is universal.
				ignore: ['css-sel2', 'css-overflow'],
			},
		],
	},
};

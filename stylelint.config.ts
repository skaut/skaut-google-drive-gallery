/** @type {import('stylelint').Config} */
export default {
	extends: [
		'stylelint-config-standard',
		'@wordpress/stylelint-config/stylistic',
	],
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
	},
};

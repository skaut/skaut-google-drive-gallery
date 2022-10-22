/* eslint-env node */

const TerserPlugin = require('terser-webpack-plugin');

module.exports = {
	mode: 'production',
	module: {
		rules: [
			{
				test: /\.ts$/,
				use: {
					loader: 'ts-loader',
					options: {
						onlyCompileBundledFiles: true,
					},
				},
			},
		],
	},
	externals: {
		'@wordpress/block-editor': 'global wp["block-editor"]',
		'@wordpress/blocks': 'global wp.blocks',
		'@wordpress/components': 'global wp.components',
		'@wordpress/editor': 'global wp.editor',
		'@wordpress/element': 'global wp.element',
		jquery: 'commonjs jQuery',
		'justified-layout': 'commonjs justified-layout',
	},
	resolve: {
		extensions: ['.ts', '.js'],
	},
	output: {
		filename: '[name].js',
	},
	optimization: {
		minimize: false,
		minimizer: [
			new TerserPlugin({
				extractComments: false,
				terserOptions: {
					format: {
						comments: false,
					},
				},
			}),
		],
	},
};

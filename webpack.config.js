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
		'@wordpress/block-editor': 'var wp.blockEditor',
		'@wordpress/blocks': 'var wp.blocks',
		'@wordpress/components': 'var wp.components',
		'@wordpress/editor': 'var wp.editor',
		'@wordpress/element': 'var wp.element',
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
		minimize: true,
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

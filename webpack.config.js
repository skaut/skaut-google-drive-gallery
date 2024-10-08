/* eslint-env node */

import TerserPlugin from 'terser-webpack-plugin';

export default {
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
		imagelightbox: 'var imagelightbox',
		jquery: 'var jQuery',
		'justified-layout': 'commonjs justified-layout',
		tinymce: 'var tinymce',
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

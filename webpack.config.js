/* eslint-env node */

import TerserPlugin from 'terser-webpack-plugin';
import webpack from 'webpack';

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
	entry: {
		root_selection: {
			import: './src/ts/admin/root_selection.ts',
			filename: 'admin/js/root_selection.min.js',
		},
		tinymce: {
			import: './src/ts/admin/tinymce.ts',
			filename: 'admin/js/tinymce.min.js',
		},
		block: {
			import: './src/ts/frontend/block.ts',
			filename: 'frontend/js/block.min.js',
		},
		shortcode: {
			import: './src/ts/frontend/shortcode.ts',
			filename: 'frontend/js/shortcode.min.js',
		},
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
	plugins: [
		new webpack.BannerPlugin({
			banner: 'jQuery( function( $ ) {\n',
			raw: true,
		}),
		new webpack.BannerPlugin({
			banner: '} );\n',
			footer: true,
			raw: true,
		}),
	],
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

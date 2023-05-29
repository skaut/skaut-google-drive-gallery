/* eslint-env node */

import { Transform } from 'node:stream';

import gulp from 'gulp';
import cleanCSS from 'gulp-clean-css';
import inject from 'gulp-inject-string';
import rename from 'gulp-rename';
import shell from 'gulp-shell';
import named from 'vinyl-named';
import webpack from 'webpack-stream';

import webpackConfig from './webpack.config.js';

gulp.task('build:css:admin', () =>
	gulp
		.src(['src/css/admin/*.css'])
		.pipe(cleanCSS())
		.pipe(rename({ suffix: '.min' }))
		.pipe(gulp.dest('dist/admin/css/'))
);

gulp.task('build:css:frontend', () =>
	gulp
		.src(['src/css/frontend/*.css'])
		.pipe(cleanCSS())
		.pipe(rename({ suffix: '.min' }))
		.pipe(gulp.dest('dist/frontend/css/'))
);

gulp.task('build:css', gulp.parallel('build:css:admin', 'build:css:frontend'));

gulp.task(
	'build:deps:composer:scoper',
	shell.task('vendor/bin/php-scoper add-prefix --force')
);

gulp.task(
	'build:deps:composer:autoloader',
	gulp.series(
		shell.task(
			'composer dump-autoload --no-dev' +
				(process.env.NODE_ENV === 'production' ? ' -o' : '')
		),
		() =>
			gulp
				.src(['vendor/composer/autoload_static.php'])
				.pipe(
					new Transform({
						objectMode: true,
						transform: (chunk, encoding, callback) => {
							let contents = String(chunk.contents).split('\n');
							let mode = 'none';
							contents = contents.map((line) => {
								if (/^\s*\);$/g.exec(line)) {
									mode = 'none';
								} else if (
									/^\s*public static \$prefixDirsPsr4 = array \($/.exec(
										line
									)
								) {
									mode = 'prefixDirs';
								} else if (
									/^\s*public static \$classMap = array \($/.exec(
										line
									)
								) {
									mode = 'classMap';
								} else if (mode === 'prefixDirs') {
									line = line.replace(
										/^(\s*)'([^']*)\\\\' => $/,
										"$1'Sgdg\\\\Vendor\\\\$2\\\\' => "
									);
								} else if (mode === 'classMap') {
									line = line.replace(
										/^(\s*)'([^']*)' =>/,
										"$1'Sgdg\\\\Vendor\\\\$2' =>"
									);
								} else {
									line = line.replace(
										'namespace Composer\\Autoload;',
										'namespace Sgdg\\Vendor\\Composer\\Autoload;'
									);
								}
								return line;
							});
							chunk.contents = Buffer.from(
								contents.join('\n'),
								encoding
							);
							callback(null, chunk);
						},
					})
				)
				.pipe(gulp.dest('dist/vendor/composer/')),
		shell.task('composer dump-autoload')
	)
);

gulp.task(
	'build:deps:composer',
	gulp.series('build:deps:composer:scoper', 'build:deps:composer:autoloader')
);

gulp.task('build:deps:npm:imagelightbox', () =>
	gulp
		.src([
			'node_modules/imagelightbox/dist/imagelightbox.css',
			'node_modules/imagelightbox/dist/imagelightbox.umd.cjs',
		])
		.pipe(gulp.dest('dist/bundled/'))
);

gulp.task('build:deps:npm:imagesloaded', () =>
	gulp
		.src('node_modules/imagesloaded/imagesloaded.pkgd.min.js')
		.pipe(gulp.dest('dist/bundled/'))
);

gulp.task(
	'build:deps:npm:justified-layout',
	gulp.series(
		shell.task(['npm install --production=false'], {
			cwd: 'node_modules/justified-layout',
		}),
		shell.task(['npm run build'], {
			cwd: 'node_modules/justified-layout',
		}),
		() =>
			gulp
				.src(
					'node_modules/justified-layout/dist/justified-layout.min.*'
				)
				.pipe(gulp.dest('dist/bundled/'))
	)
);

gulp.task(
	'build:deps:npm',
	gulp.parallel(
		'build:deps:npm:imagelightbox',
		'build:deps:npm:imagesloaded',
		'build:deps:npm:justified-layout'
	)
);

gulp.task('build:deps', gulp.parallel('build:deps:composer', 'build:deps:npm'));

gulp.task('build:js:admin', () =>
	gulp
		.src(['src/ts/admin/root_selection.ts', 'src/ts/admin/tinymce.ts'])
		.pipe(named((file) => file.stem + '.min'))
		.pipe(webpack(webpackConfig))
		.pipe(inject.prepend('jQuery( function( $ ) {\n'))
		.pipe(inject.append('} );\n'))
		.pipe(gulp.dest('dist/admin/js/'))
);

gulp.task('build:js:frontend', () =>
	gulp
		.src(['src/ts/frontend/block.ts', 'src/ts/frontend/shortcode.ts'])
		.pipe(named((file) => file.stem + '.min'))
		.pipe(webpack(webpackConfig))
		.pipe(inject.prepend('jQuery( function( $ ) {\n'))
		.pipe(inject.append('} );\n'))
		.pipe(gulp.dest('dist/frontend/js/'))
);

gulp.task('build:js', gulp.parallel('build:js:admin', 'build:js:frontend'));

gulp.task('build:php:admin', () =>
	gulp.src(['src/php/admin/**/*.php']).pipe(gulp.dest('dist/admin/'))
);

gulp.task('build:php:base', () =>
	gulp.src(['src/php/*.php']).pipe(gulp.dest('dist/'))
);

gulp.task('build:php:exceptions', () =>
	gulp
		.src(['src/php/exceptions/**/*.php'])
		.pipe(gulp.dest('dist/exceptions/'))
);

gulp.task('build:php:frontend', () =>
	gulp.src(['src/php/frontend/**/*.php']).pipe(gulp.dest('dist/frontend/'))
);

gulp.task('build:php:helpers', () =>
	gulp.src(['src/php/helpers/**/*.php']).pipe(gulp.dest('dist/helpers/'))
);

gulp.task(
	'build:php',
	gulp.parallel(
		'build:php:admin',
		'build:php:base',
		'build:php:exceptions',
		'build:php:frontend',
		'build:php:helpers'
	)
);

gulp.task('build:png', () =>
	gulp.src(['src/png/icon.png']).pipe(gulp.dest('dist/admin/'))
);

gulp.task('build:txt', () =>
	gulp.src(['src/txt/*.txt']).pipe(gulp.dest('dist/'))
);

gulp.task(
	'build',
	gulp.parallel(
		'build:css',
		'build:deps',
		'build:js',
		'build:php',
		'build:png',
		'build:txt'
	)
);

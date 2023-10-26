/* eslint-env node */

const gulp = require('gulp');

const cleanCSS = require('gulp-clean-css');
const inject = require('gulp-inject-string');
const merge = require('merge-stream');
const named = require('vinyl-named');
const rename = require('gulp-rename');
const replace = require('gulp-replace');
const shell = require('gulp-shell');
const webpack = require('webpack-stream');

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
			merge(
				gulp.src([
					'vendor/composer/autoload_classmap.php',
					'vendor/composer/autoload_files.php',
					'vendor/composer/autoload_namespaces.php',
					'vendor/composer/autoload_psr4.php',
				]),
				gulp
					.src(['vendor/composer/autoload_static.php'])
					.pipe(
						replace(
							'namespace Composer\\Autoload;',
							'namespace Sgdg\\Vendor\\Composer\\Autoload;'
						)
					)
					.pipe(
						replace(
							/'(.*)\\\\' => \n/g,
							"'Sgdg\\\\Vendor\\\\$1\\\\' => \n"
						)
					)
			).pipe(gulp.dest('dist/vendor/composer/')),
		shell.task('composer dump-autoload')
	)
);

gulp.task(
	'build:deps:composer',
	gulp.series('build:deps:composer:scoper', 'build:deps:composer:autoloader')
);

gulp.task('build:deps:npm:imagelightbox', () =>
	gulp
		.src('node_modules/imagelightbox/dist/imagelightbox.min.*')
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
		.pipe(webpack(require('./webpack.config.js')))
		.pipe(inject.prepend('jQuery( function( $ ) {\n'))
		.pipe(inject.append('} );\n'))
		.pipe(gulp.dest('dist/admin/js/'))
);

gulp.task('build:js:frontend', () =>
	gulp
		.src(['src/ts/frontend/block.ts', 'src/ts/frontend/shortcode.ts'])
		.pipe(named((file) => file.stem + '.min'))
		.pipe(webpack(require('./webpack.config.js')))
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

/* eslint-env node */

const gulp = require('gulp');

const cleanCSS = require('gulp-clean-css');
const concat = require('gulp-concat');
const inject = require('gulp-inject-string');
const merge = require('merge-stream');
const rename = require('gulp-rename');
const replace = require('gulp-replace');
const shell = require('gulp-shell');
const terser = require('gulp-terser');
const ts = require('gulp-typescript');

gulp.task('build:css:admin', function () {
	return gulp
		.src(['src/css/admin/*.css'])
		.pipe(cleanCSS())
		.pipe(rename({ suffix: '.min' }))
		.pipe(gulp.dest('dist/admin/css/'));
});

gulp.task('build:css:frontend', function () {
	return gulp
		.src(['src/css/frontend/*.css'])
		.pipe(cleanCSS())
		.pipe(rename({ suffix: '.min' }))
		.pipe(gulp.dest('dist/frontend/css/'));
});

gulp.task('build:css', gulp.parallel('build:css:admin', 'build:css:frontend'));

gulp.task(
	'build:deps:composer:scoper',
	shell.task(
		'vendor/bin/php-scoper add-prefix --force --output-dir=dist/vendor'
	)
);

gulp.task(
	'build:deps:composer:autoloader',
	gulp.series(
		shell.task(
			'composer dump-autoload --no-dev' +
				(process.env.NODE_ENV === 'production' ? ' -o' : '')
		),
		function () {
			return merge(
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
			).pipe(gulp.dest('dist/vendor/composer/'));
		},
		shell.task('composer dump-autoload')
	)
);

gulp.task(
	'build:deps:composer',
	gulp.series('build:deps:composer:scoper', 'build:deps:composer:autoloader')
);

gulp.task('build:deps:npm:imagelightbox', function () {
	return gulp
		.src('node_modules/imagelightbox/dist/imagelightbox.min.*')
		.pipe(gulp.dest('dist/bundled/'));
});

gulp.task('build:deps:npm:imagesloaded', function () {
	return gulp
		.src('node_modules/imagesloaded/imagesloaded.pkgd.min.js')
		.pipe(gulp.dest('dist/bundled/'));
});

gulp.task(
	'build:deps:npm:justified-layout',
	gulp.series(
		shell.task(['npm install --production=false'], {
			cwd: 'node_modules/justified-layout',
		}),
		shell.task(['npm run build'], {
			cwd: 'node_modules/justified-layout',
		}),
		function () {
			return gulp
				.src(
					'node_modules/justified-layout/dist/justified-layout.min.*'
				)
				.pipe(gulp.dest('dist/bundled/'));
		}
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

gulp.task('build:js:admin', function () {
	function bundle(name, sources, jQuery = false) {
		const tsProject = ts.createProject('tsconfig.json');
		let ret = gulp
			.src(sources.concat(['src/d.ts/*.d.ts']))
			.pipe(tsProject())
			.js.pipe(concat(name + '.min.js'));
		if (jQuery) {
			ret = ret
				.pipe(inject.prepend('jQuery( function( $ ) {\n'))
				.pipe(inject.append('} );\n'));
		}
		return ret.pipe(terser()).pipe(gulp.dest('dist/admin/js/'));
	}

	return merge(
		bundle(
			'root_selection',
			['src/ts/isError.ts', 'src/ts/admin/root_selection.ts'],
			true
		),
		bundle(
			'tinymce',
			['src/ts/isError.ts', 'src/ts/admin/tinymce.ts'],
			true
		)
	);
});

gulp.task('build:js:frontend', function () {
	function bundle(name, sources, jQuery = false) {
		const tsProject = ts.createProject('tsconfig.json');
		let ret = gulp
			.src(sources.concat(['src/d.ts/*.d.ts']))
			.pipe(tsProject())
			.js.pipe(concat(name + '.min.js'));
		if (jQuery) {
			ret = ret
				.pipe(inject.prepend('jQuery( function( $ ) {\n'))
				.pipe(inject.append('} );\n'));
		}
		return ret.pipe(terser()).pipe(gulp.dest('dist/frontend/js/'));
	}

	return merge(
		bundle(
			'block',
			[
				'src/ts/isError.ts',
				'src/ts/frontend/block/SgdgEditorComponent.ts',
				'src/ts/frontend/block/SgdgBlockIconComponent.ts',
				'src/ts/frontend/block.ts',
				'src/ts/frontend/block/SgdgSettingsComponent.ts',
				'src/ts/frontend/block/SgdgBooleanSettingsComponent.ts',
				'src/ts/frontend/block/SgdgIntegerSettingsComponent.ts',
				'src/ts/frontend/block/SgdgOrderingSettingsComponent.ts',
				'src/ts/frontend/block/SgdgSettingsOverrideComponent.ts',
				'src/ts/frontend/interfaces/Attributes.ts',
			],
			true
		),
		bundle(
			'shortcode',
			[
				'src/ts/isError.ts',
				'src/ts/frontend/shortcode/QueryParameter.ts',
				'src/ts/frontend/shortcode/Shortcode.ts',
				'src/ts/frontend/shortcode/ShortcodeRegistry.ts',
				'src/ts/frontend/shortcode.ts',
			],
			true
		)
	);
});

gulp.task('build:js', gulp.parallel('build:js:admin', 'build:js:frontend'));

gulp.task('build:php:admin', function () {
	return gulp.src(['src/php/admin/**/*.php']).pipe(gulp.dest('dist/admin/'));
});

gulp.task('build:php:base', function () {
	return gulp.src(['src/php/*.php']).pipe(gulp.dest('dist/'));
});

gulp.task('build:php:exceptions', function () {
	return gulp
		.src(['src/php/exceptions/**/*.php'])
		.pipe(gulp.dest('dist/exceptions/'));
});

gulp.task('build:php:frontend', function () {
	return gulp
		.src(['src/php/frontend/**/*.php'])
		.pipe(gulp.dest('dist/frontend/'));
});

gulp.task('build:php:helpers', function () {
	return gulp
		.src(['src/php/helpers/**/*.php'])
		.pipe(gulp.dest('dist/helpers/'));
});

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

gulp.task('build:png', function () {
	return gulp.src(['src/png/icon.png']).pipe(gulp.dest('dist/admin/'));
});

gulp.task('build:txt', function () {
	return gulp.src(['src/txt/*.txt']).pipe(gulp.dest('dist/'));
});

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

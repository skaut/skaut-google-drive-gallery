/* eslint-env node */

export {};

const gulp = require( 'gulp' );

const eslint = require( 'gulp-eslint' );
const shell = require( 'gulp-shell' );
const stylelint = require( 'gulp-stylelint' );

// Lints

//gulp.task( 'lint', gulp.series( 'phpcs', 'phpmd', 'phan', 'eslint', 'stylelint' ) );
gulp.task( 'lint', gulp.series( 'lint:phpcs', 'lint:phpmd', 'lint:phan', 'lint:eslint' ) );

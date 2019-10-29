var gulp = require( 'gulp' );
var shell = require( 'gulp-shell' );

// Unit tests

gulp.task( 'unit:phpunit', shell.task([ 'vendor/bin/phpunit' ]) )	;

gulp.task( 'unit', gulp.series( 'unit:phpunit' ) );

const gulp = require( 'gulp' );

require( './build' );
require( './lint' );
require( './unit' );

// Default command

gulp.task( 'default', gulp.series( 'build', 'unit', 'lint' ) );

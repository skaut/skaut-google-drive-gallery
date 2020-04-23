/* eslint-env node */

import './build';

const gulp = require( 'gulp' );

// Default command

gulp.task( 'default', gulp.series( 'build', 'unit', 'lint' ) );

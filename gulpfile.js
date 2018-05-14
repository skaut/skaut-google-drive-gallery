const gulp = require('gulp');
const npmcheck = require('gulp-npm-check');

gulp.task('npm-check-updates', function(cb)
	{
		return npmcheck({skipUnused: true, ignore: ['jquery']}, cb);
	});

gulp.task('default', ['npm-check-updates']);

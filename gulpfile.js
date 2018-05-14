const gulp = require("gulp");
const npmcheck = require("gulp-npm-check");


gulp.task("npm-check-updates", function(done)
	{
		return npmcheck({"skipUnused": true, "ignore": ["jquery"]}, done);
	});

gulp.task("default", gulp.series("npm-check-updates"));

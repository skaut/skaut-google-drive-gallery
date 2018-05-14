const gulp = require("gulp");
const npmcheck = require("gulp-npm-check");
const composer = require("gulp-composer");

gulp.task("composer-check-updates", function(done)
	{
		composer("show -l", {"self-install": false, "async": false})
		done();
	});

gulp.task("npm-check-updates", function(done)
	{
		return npmcheck({"skipUnused": true, "ignore": ["jquery"]}, done);
	});

gulp.task("default", gulp.series("composer-check-updates", "npm-check-updates"));

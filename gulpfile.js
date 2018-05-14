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
		npmcheck({"skipUnused": true, "ignore": ["jquery"]}, done);
	});

function npmDoUpdate(done)
{
	npmcheck({"skipUnused": true, "ignore": ["jquery"], "update": true, "ignoreDev": true}, done);
}

function copyImagelightbox()
{
	return gulp.src("node_modules/imagelightbox/dist/imagelightbox.min.*")
		.pipe(gulp.dest("plugin/bundled/"));
}

function copyImagesloaded()
{
	return gulp.src("node_modules/imagesloaded/imagesloaded.pkgd.min.js")
		.pipe(gulp.dest("plugin/bundled/"));
}

function copyMasonry()
{
	return gulp.src("node_modules/masonry-layout/dist/masonry.pkgd.min.js")
		.pipe(gulp.dest("plugin/bundled/"));
}

gulp.task("npm-update", gulp.series(npmDoUpdate, gulp.parallel(copyImagelightbox, copyImagesloaded, copyMasonry)));

gulp.task("default", gulp.series("composer-check-updates", "npm-check-updates"));

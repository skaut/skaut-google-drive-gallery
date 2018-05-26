const gulp = require("gulp");
const npmcheck = require("gulp-npm-check");
const composer = require("gulp-composer");
const shell = require("gulp-shell");
const replace = require('gulp-replace');

gulp.task("composer-check-updates", function(done)
	{
		composer("show -l", {"self-install": false, "async": false})
		done();
	});

gulp.task("npm-check-updates", function(done)
	{
		npmcheck({"skipUnused": true}, done);
	});

gulp.task("composer-do-update", function(done)
	{
		composer("update ", {"self-install": false, "async": false})
		done();
	});

gulp.task("composer-copy-apiclient", function()
	{
		return gulp.src(["vendor/google/apiclient/src/Google/*", "vendor/google/apiclient/src/Google/AccessToken/Revoke.php", "vendor/google/apiclient/src/Google/AuthHandler/AuthHandlerFactory.php", "vendor/google/apiclient/src/Google/AuthHandler/Guzzle6AuthHandler.php", "vendor/google/apiclient/src/Google/Http/REST.php", "vendor/google/apiclient/src/Google/Service/*", "vendor/google/apiclient/src/Google/Task/Runner.php", "vendor/google/apiclient/src/Google/Utils/*", "!**/autoload.php", "!**/README*"], {base: "vendor/"})
			.pipe(replace(/^<\?php/, "<?php\nnamespace Sgdg\\Vendor;"))
			.pipe(replace(/\nuse /g, "\nuse Sgdg\\Vendor\\"))
			.pipe(gulp.dest("plugin/bundled/vendor/"));
	})

gulp.task("composer-copy-apiclient-services", function()
	{
		return gulp.src(["vendor/google/apiclient-services/src/Google/Service/Drive.php", "vendor/google/apiclient-services/src/Google/Service/Drive/DriveFile.php", "vendor/google/apiclient-services/src/Google/Service/Drive/FileList.php", "vendor/google/apiclient-services/src/Google/Service/Drive/TeamDrive.php", "vendor/google/apiclient-services/src/Google/Service/Drive/TeamDriveList.php", "vendor/google/apiclient-services/src/Google/Service/Drive/Resource/*"], {base: "vendor/"})
			.pipe(replace(/^<\?php/, "<?php\nnamespace Sgdg\\Vendor;"))
			.pipe(replace(/\nuse /g, "\nuse Sgdg\\Vendor\\"))
			.pipe(gulp.dest("plugin/bundled/vendor/"));
	})

gulp.task("composer-copy-other", function()
	{
		return gulp.src(["vendor/google/auth/src/Cache/Item.php", "vendor/google/auth/src/Cache/MemoryCacheItemPool.php", "vendor/google/auth/src/CacheTrait.php", "vendor/google/auth/src/FetchAuthTokenInterface.php", "vendor/google/auth/src/OAuth2.php", "vendor/google/auth/src/HttpHandler/Guzzle6HttpHandler.php", "vendor/google/auth/src/HttpHandler/HttpHandlerFactory.php", "vendor/google/auth/src/Middleware/ScopedAccessTokenMiddleware.php"], {base: "vendor/"})
			.pipe(replace(/\nnamespace /g, "\nnamespace Sgdg\\Vendor\\"))
			.pipe(replace(/\nuse /g, "\nuse Sgdg\\Vendor\\"))
			.pipe(gulp.dest("plugin/bundled/vendor/"));
	})

gulp.task("composer-copy-licenses", function()
	{
		return gulp.src(["vendor/google/apiclient-services/LICENSE", "vendor/google/apiclient/LICENSE", "vendor/google/auth/LICENSE", "vendor/guzzlehttp/guzzle/LICENSE", "vendor/guzzlehttp/promises/LICENSE", "vendor/guzzlehttp/psr7/LICENSE", "vendor/monolog/monolog/LICENSE", "vendor/psr/cache/LICENSE.txt", "vendor/psr/http-message/LICENSE", "vendor/psr/log/LICENSE"], {base: "vendor/"})
			.pipe(gulp.dest("plugin/bundled/vendor/"));
	})

gulp.task("composer-copy", gulp.parallel("composer-copy-apiclient", "composer-copy-apiclient-services", "composer-copy-other", "composer-copy-licenses"))

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

gulp.task("composer-update", gulp.series("composer-do-update", "composer-copy"));

gulp.task("npm-update", gulp.series(shell.task(["npm update"]), gulp.parallel(copyImagelightbox, copyImagesloaded, copyMasonry)));

gulp.task("phpcs", shell.task(["vendor/squizlabs/php_codesniffer/bin/phpcs"]));

gulp.task("default", gulp.series("phpcs", "composer-check-updates", "npm-check-updates"));

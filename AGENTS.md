# AGENTS.md

This file provides guidance to coding agents when working with code in this repository.

## Project

`skaut-google-drive-gallery` — a WordPress plugin rendering image/video galleries backed by Google Drive.
Published on wordpress.org; docs live at https://napoveda.skaut.cz/dobryweb/en-skaut-google-drive-gallery.

Hard constraints that shape most of the code:

- **PHP 5.6 compatibility** (`composer.json` `require`, `phpcs.xml` `testVersion 5.6-8.1`). No short arrays only, no
  arrow functions, no typed properties/params, no null coalesce (that Slevomat rule is explicitly excluded). Composer
  dependencies are pinned to old versions for the same reason, and the CI `platform-check` job verifies it.
- **WordPress 4.9.6 minimum**, text domain `skaut-google-drive-gallery`, global prefix `Sgdg`.
- Vendored PHP dependencies are namespace-prefixed to `Sgdg\Vendor\…` by php-scoper at build time. In `src/php` always
  refer to third-party classes through the prefixed namespace (e.g. `use Sgdg\Vendor\GuzzleHttp\Promise\PromiseInterface;`).

## Build

`src/` is never loaded directly — everything runs from `dist/`, which `npm run build` assembles:

- `build:css` — gulp: minifies `src/css/{admin,frontend}/*.css` → `dist/{admin,frontend}/css/*.min.css`
- `build:js:*` — one Vite config per entry point (`block`, `shortcode`, `root_selection`, `tinymce`), each an IIFE bundle
  with jQuery / `wp.*` / tinymce / imagelightbox / justified-layout kept **external** and mapped to globals
  (see `vite-builder.config.ts`). Adding an entry point means adding a `*.vite.config.ts` and a `build:js:*` script.
- `build:deps:composer` — runs php-scoper (`scoper.inc.php`), then rewrites the Composer classmap autoloader into the
  `Sgdg\Vendor\` namespace (the transform in `gulpfile.js`)
- `build:deps:npm` — copies runtime npm bundles into `dist/bundled/`
- `build:php`, `build:png`, `build:txt` — plain copies of `src/php/**`, the icon and `readme.txt`/`license.txt`

`npm run clean` (via `prebuild`) wipes `dist/`.

## Lint & test

```
npm run lint                 # everything, in parallel
npm run lint:php:phpcs       # WPCS + Slevomat + PHPCompatibilityWP (phpcs.xml)
npm run lint:php:phpstan     # phpstan.neon
npm run lint:php:phan        # .phan/config.php (needs the ast PHP extension)
npm run lint:php:phpmd       # phpmd.xml
npm run lint:ts              # eslint + tsc --noEmit
npm run lint:css             # stylelint
vendor/bin/phpcbf            # auto-fix PHP style
```

Tests are WordPress integration tests via `phpunit.xml`, and they load **`dist/`**, not `src/`:

```
./bin/install-wp-tests.sh wordpress_test root '' 127.0.0.1 <wp-version>   # once, needs a MySQL server
npm run build                                                            # required before testing
npm run test                                                             # or: vendor/bin/phpunit
vendor/bin/phpunit --filter Readonly_String_Option                       # single test
```

`npm run test:php:phpunit` has pre/post hooks that remove `vendor/google` and re-dump the autoloader (so the scoped
`dist/vendor` copy is used) and then restore `vendor/` with `composer install` afterwards. Running `vendor/bin/phpunit`
directly skips that. Coverage requires `forceCoversAnnotation` — every test needs a `@covers`.

`npm run plugin-check` runs the wordpress.org Plugin Check plugin against `dist/` inside `wp-env`.

## Architecture

`src/php/skaut-google-drive-gallery.php` is the plugin entry: it `require_once`s every class explicitly (no PSR-4
autoloading for plugin code — **new PHP files must be added to that require list** and to the matching `build:php:*`
gulp task if a new directory is introduced), then instantiates `Sgdg\Main`.

`Sgdg\Main` constructs one object per feature; each constructor only registers WordPress hooks
(`@phan-constructor-used-for-side-effects` marks this pattern):

| Class | Role |
| --- | --- |
| `Frontend\Shortcode` | registers the `[sgdg]` shortcode, its scripts and styles |
| `Frontend\Block` | Gutenberg block registration (editor UI in `src/ts/frontend/block*`) |
| `Frontend\Gallery` | `wp_ajax_gallery` — directory path + first page when a gallery loads or navigates |
| `Frontend\Page` | `wp_ajax_page` — one page of directories/images/videos (`frontend/page/class-*.php`) |
| `Frontend\Video_Proxy` | `wp_ajax_video_proxy` — streams Drive videos through WordPress |
| `Admin\Settings_Pages` | admin screens under `admin/settings-pages/`, incl. `wp_ajax_list_gdrive_dir` |
| `Admin\TinyMCE_Plugin` | classic-editor shortcode dialog |

AJAX handlers all follow the same shape: `handle_ajax()` → `Helpers::ajax_wrapper( 'ajax_handler_body' )`, which
translates the typed exceptions in `src/php/exceptions/` into JSON error responses. Add new failure modes as exception
classes there rather than returning error strings.

Google Drive access goes through `API_Client` (low-level: batching + Guzzle promises) and `API_Facade` (typed queries).
`API_Client::async_request` / `async_paginated_request` add requests to a shared Google batch and return promises;
`API_Client::execute()` flushes the batch and resolves them. Rate-limit failures surface as
`API_Rate_Limit_Exception`. Requested Drive fields are declared in `Frontend\API_Fields`.

Options are objects, not raw `get_option` calls: `Sgdg\Options` holds a static property per setting, each an instance of
an option class in `src/php/frontend/` (`Boolean_Option`, `Bounded_Integer_Option`, `Ordering_Option`,
`Root_Path_Option`, …) that knows its own name, default, sanitization and admin rendering. `Options_Proxy` layers
shortcode/block attribute overrides on top of the global values — frontend code should read through the proxy it is
handed, not `Options` directly.

## TypeScript frontend

`src/ts/frontend/shortcode/` is the runtime gallery: `ShortcodeRegistry` finds every `.sgdg-gallery-container`, keys
instances by the first 8 chars of the gallery hash, and fans out reflow/lightbox events; `Shortcode` owns one gallery
(fetching via the AJAX endpoints, justified layout, imagelightbox). Shapes of the AJAX JSON and the `wp_localize_script`
payloads are declared as ambient types in `src/d.ts/*.d.ts` — keep those in sync when changing an endpoint's response.

tsconfig is strict with `exactOptionalPropertyTypes`, `noUnusedLocals/Parameters` and
`noPropertyAccessFromIndexSignature`. ESLint enforces the WordPress config plus `simple-import-sort`,
`prefer-arrow-functions`, `eslint-plugin-compat` (browserslist = `@wordpress/browserslist-config`) and Prettier
(tabs, single quotes).

## Version bumps

The version appears in `package.json`, the plugin header in `src/php/skaut-google-drive-gallery.php`, and
`src/txt/readme.txt` — update all of them together.

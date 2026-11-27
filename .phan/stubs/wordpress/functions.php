<?php
/**
 * Contains stubs of WordPress functions missing from skaut/wordpress-stubs
 *
 * @package skaut-google-drive-gallery
 *
 * @phan-file-suppress PhanPluginAlwaysReturnFunction
 * @phan-file-suppress PhanTypeMissingReturn
 * @phan-file-suppress PhanUnusedGlobalFunctionParameter
 *
 * phpcs:disable Generic.Commenting.DocComment.MissingShort
 * phpcs:disable SlevomatCodingStandard.Functions.DisallowEmptyFunction.EmptyFunction
 * phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
 * phpcs:disable Squiz.Commenting.FunctionComment.MissingParamComment
 * phpcs:disable Squiz.Commenting.FunctionComment.InvalidNoReturn
 * phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound
 */

/**
 * @param array<string, mixed>|string $args
 *
 * @return array<int|object>
 * @phpstan-return ($args is array{fields: 'ids', ...} ? array<int> : array<object>)
 */
function get_sites( $args = array() ) {
}

/** @return bool */
function is_multisite() {
}

/**
 * @param string $text
 * @param string $locale
 *
 * @return string
 */
function remove_accents( $text, $locale = '' ) {
}

/** @return bool */
function restore_current_blog() {
}

/**
 * @param int  $new_blog_id
 * @param bool $deprecated
 *
 * @return true
 */
function switch_to_blog( $new_blog_id, $deprecated = null ) {
}

/**
 * @param string                                          $content
 * @param array<string, array<string, mixed>|true>|string $allowed_html
 * @param array<string>                                   $allowed_protocols
 *
 * @return string
 */
function wp_kses( $content, $allowed_html, $allowed_protocols = array() ) {
}

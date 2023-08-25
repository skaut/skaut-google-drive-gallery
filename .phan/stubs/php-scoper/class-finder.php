<?php
/**
 * Contains Isolated\Symfony\Component\Finder\Finder class stub for phan
 *
 * @package skaut-google-drive-gallery
 *
 * @phan-file-suppress PhanPluginAlwaysReturnMethod
 * @phan-file-suppress PhanPluginPossiblyStaticPublicMethod
 * @phan-file-suppress PhanTypeMissingReturn
 * @phan-file-suppress PhanUnusedPublicNoOverrideMethodParameter
 * phpcs:disable Generic.Commenting.DocComment.Empty
 * phpcs:disable Generic.Commenting.DocComment.MissingShort
 * phpcs:disable SlevomatCodingStandard.Classes.RequireAbstractOrFinal.ClassNeitherAbstractNorFinal
 * phpcs:disable SlevomatCodingStandard.Commenting.EmptyComment.EmptyComment
 * phpcs:disable SlevomatCodingStandard.Commenting.RequireOneLineDocComment.MultiLineDocComment
 * phpcs:disable SlevomatCodingStandard.Functions.DisallowEmptyFunction.EmptyFunction
 * phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
 * phpcs:disable Squiz.Commenting.FunctionComment.EmptyThrows
 * phpcs:disable Squiz.Commenting.FunctionComment.InvalidNoReturn
 * phpcs:disable Squiz.Commenting.FunctionComment.MissingParamComment
 * phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedNamespaceFound
 * phpcs:disable WordPress.NamingConventions.ValidVariableName.VariableNotSnakeCase
 */

namespace Isolated\Symfony\Component\Finder;

use Closure;
use Countable;
use InvalidArgumentException;
use Isolated\Symfony\Component\Finder\Exception\DirectoryNotFoundException;
use Iterator;
use IteratorAggregate;
use LogicException;
use SplFileInfo;

/**
 */
class Finder implements Countable, IteratorAggregate {

	/**
	 * @return static
	 */
	public static function create() {
	}

	/**
	 * @return $this
	 */
	public function directories() {
	}

	/**
	 * @return $this
	 */
	public function files() {
	}

	/**
	 * @param string|int|array<string>|array<int> $levels
	 *
	 * @return $this
	 */
	public function depth( $levels ) {
	}

	/**
	 * @param string|array<string> $dates
	 *
	 * @return $this
	 */
	public function date( $dates ) {
	}

	/**
	 * @param string|array<string> $patterns
	 *
	 * @return $this
	 */
	public function name( $patterns ) {
	}

	/**
	 * @param string|array<string> $patterns
	 *
	 * @return $this
	 */
	public function notName( $patterns ) {
	}

	/**
	 * @param string|array<string> $patterns
	 *
	 * @return $this
	 */
	public function contains( $patterns ) {
	}

	/**
	 * @param string|array<string> $patterns
	 *
	 * @return $this
	 */
	public function notContains( $patterns ) {
	}

	/**
	 * @param string|array<string> $patterns
	 *
	 * @return $this
	 */
	public function path( $patterns ) {
	}

	/**
	 * @param string|array<string> $patterns
	 *
	 * @return $this
	 */
	public function notPath( $patterns ) {
	}

	/**
	 * @param string|int|array<string>|array<int> $sizes
	 *
	 * @return $this
	 */
	public function size( $sizes ) {
	}

	/**
	 * @param string|array<string> $dirs
	 *
	 * @return $this
	 */
	public function exclude( $dirs ) {
	}

	/**
	 * @param bool $ignoreDotFiles
	 *
	 * @return $this
	 */
	public function ignoreDotFiles( $ignoreDotFiles ) {
	}

	/**
	 * @param bool $ignoreVCS
	 *
	 * @return $this
	 */
	public function ignoreVCS( $ignoreVCS ) {
	}

	/**
	 * @param bool $ignoreVCSIgnored
	 *
	 * @return $this
	 */
	public function ignoreVCSIgnored( $ignoreVCSIgnored ) {
	}

	/**
	 * @param Closure $closure
	 *
	 * @return $this
	 */
	public function sort( $closure ) {
	}

	/**
	 * @param bool $useNaturalSort
	 *
	 * @return $this
	 */
	public function sortByName( $useNaturalSort ) {
	}

	/**
	 * @return $this
	 */
	public function sortByType() {
	}

	/**
	 * @return $this
	 */
	public function sortByAccessedTime() {
	}

	/**
	 * @return $this
	 */
	public function reverseSorting() {
	}

	/**
	 * @return $this
	 */
	public function sortByChangedTime() {
	}

	/**
	 * @return $this
	 */
	public function sortByModifiedTime() {
	}

	/**
	 * @param Closure $closure
	 *
	 * @return $this
	 */
	public function filter( $closure ) {
	}

	/**
	 * @return $this
	 */
	public function followLinks() {
	}

	/**
	 * @param bool $ignore
	 *
	 * @return $this
	 */
	public function ignoreUnreadableDirs( $ignore = true ) {
	}

	/**
	 * @param string|array<string> $dirs
	 *
	 * @return $this
	 *
	 * @throws DirectoryNotFoundException
	 */
	public function in( $dirs ) {
	}

	/**
	 * @return Iterator|array<SplFileInfo>
	 *
	 * @throws LogicException
	 */
	public function getIterator() {
	}

	/**
	 * @param iterable<string|SplFileInfo> $iterator
	 *
	 * @return $this
	 *
	 * @throws InvalidArgumentException
	 */
	public function append( $iterator ) {
	}

	/**
	 * @return bool
	 */
	public function hasResults() {
	}

	/**
	 * @return int
	 */
	public function count() {
	}

	/**
	 * @param string|array<string> $pattern
	 */
	public static function addVCSPattern( $pattern ) {
	}
}

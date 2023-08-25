<?php
/**
 * Contains the Readonly_String_Option_Test class
 *
 * @package skaut-google-drive-gallery
 */

use Sgdg\Admin\Readonly_String_Option;

/**
 * Contains unit tests for the Readonly_String_Option class
 */
final class Readonly_String_Option_Test extends WP_UnitTestCase {

	/**
	 * Tests the constructor
	 *
	 * @covers Sgdg\Admin\Readonly_String_Option::__construct()
	 *
	 * @return void
	 */
	public function test_ctor() {
		$option = new Readonly_String_Option( 'name', 'value', 'page', 'section', 'title' );
		$this->assertInstanceOf( '\Sgdg\Admin\Readonly_String_Option', $option );
	}
}

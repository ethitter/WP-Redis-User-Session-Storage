<?php
/**
 * Test plugin features.
 *
 * @package Redis_User_Session_Storage
 */

namespace Redis_User_Session_Storage\Tests\Inc;

use Redis;
use Redis_User_Session_Storage\Plugin;
use WP_Session_Tokens;
use WP_UnitTestCase;

/**
 * Tests for main plugin class.
 *
 * @coversDefaultClass \Redis_User_Session_Storage\Plugin
 */
class Test_Plugin extends WP_UnitTestCase {
	/**
	 * Test construction.
	 *
	 * @covers ::__construct()
	 * @return void
	 */
	public function test__construct() {
		$user_id      = $this->factory->user->create();
		$this->plugin = new Plugin( $user_id );

		$this->assertTrue( class_exists( Redis::class, false ) );

		$this->assertInstanceOf( Plugin::class, $this->plugin );
		$this->assertInstanceOf( WP_Session_Tokens::class, $this->plugin );

		$this->assertEquals( 'wpruss', $this->plugin->prefix );
	}
}

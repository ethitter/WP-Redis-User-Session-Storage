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
 * @coversDefaultClass \Redis_User_Session_Storage\Plugin
 */
class TestPlugin extends WP_UnitTestCase {
	protected $plugin;

	public function set_up() {
		// TODO: use reflection to make this more useful. Create a helper, stop initializing here.
		$this->plugin = new Plugin( 0 );
	}

	public function test__construct() {
		$this->assertTrue( class_exists( Redis::class, false ) );

		$this->assertInstanceOf( Plugin::class, $this->plugin );
		$this->assertInstanceOf( WP_Session_Tokens::class, $this->plugin );

		$this->assertEquals( 'wpruss', $this->plugin->prefix );
	}
}

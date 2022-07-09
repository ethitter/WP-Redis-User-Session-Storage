<?php
/**
 * Test plugin features.
 *
 * @package Redis_User_Session_Storage
 */

namespace Redis_User_Session_Storage\Tests\Inc;

use Redis;
use Redis_User_Session_Storage\Plugin;
use ReflectionClass;
use WP_Session_Tokens;
use WP_UnitTestCase;

/**
 * Tests for main plugin class.
 *
 * @coversDefaultClass \Redis_User_Session_Storage\Plugin
 */
class Test_Plugin extends WP_UnitTestCase {
	/**
	 * Clear stored sessions after each test, as factory can create user with
	 * same ID as previous test.
	 */
	public function tear_down() {
		parent::tear_down();

		$this->_invoke_method( 0, 'flush_redis_db' );
	}

	/**
	 * Test construction.
	 *
	 * @covers ::__construct()
	 * @return void
	 */
	public function test__construct() {
		$user_id = $this->factory->user->create();
		$object  = new Plugin( $user_id );

		$this->assertInstanceOf(
			WP_Session_Tokens::class,
			$object,
			'Failed to assert that plugin class is an instance of `WP_Session_Tokens`.'
		);

		$this->assertEquals( 'wpruss', $object->prefix );

		$this->assertTrue(
			$this->_get_property( $user_id, 'redis_connected' ),
			'Failed to assert that Redis is connected.'
		);

		$this->assertInstanceOf(
			Redis::class,
			$this->_get_property( $user_id, 'redis' ),
			'Failed to assert that Redis client is an instance of `Redis`.'
		);
	}

	/**
	 * Test `get_sessions()` method.
	 *
	 * @covers ::get_sessions()
	 * @return void
	 */
	public function test_get_sessions() {
		$user_id = $this->factory->user->create();
		$plugin  = new Plugin( $user_id );

		$this->assertEmpty(
			$this->_invoke_method( $user_id, 'get_sessions' ),
			'Failed to assert that no sessions are returned before user logs in.'
		);

		$plugin->create( time() + 60 );

		$this->assertNotEmpty(
			$this->_invoke_method( $user_id, 'get_sessions' ),
			'Failed to assert that session token is stored in Redis.'
		);
	}

	/**
	 * Test `prepare_session()` method.
	 *
	 * @covers ::prepare_session()
	 * @return void
	 */
	public function test_prepare_session() {
		$this->assertEquals(
			array(
				'expiration' => 1,
			),
			$this->_invoke_method(
				0,
				'prepare_session',
				array(
					1,
				)
			),
			'Failed to assert that session data is transformed as expected.'
		);

		$test_data = array(
			'expiration' => 2,
			'foo'        => 'bar',
		);

		$this->assertEquals(
			$test_data,
			$this->_invoke_method(
				0,
				'prepare_session',
				array(
					$test_data,
				)
			),
			'Failed to assert that session data is not transformed if it is already prepared.'
		);
	}

	/**
	 * Test `get_session()` method.
	 *
	 * @covers ::get_session()
	 * @return void
	 */
	public function test_get_session() {
		$user_id = $this->factory->user->create();
		$plugin  = new Plugin( $user_id );

		$this->assertEmpty(
			$this->_invoke_method(
				$user_id,
				'get_session',
				array(
					'abcdef0123456789',
				)
			),
			'Failed to assert that arbitrary verifier does not return a session.'
		);

		$expiration = time() + 60;

		$plugin->create( $expiration );
		$tokens   = $this->_invoke_method( $user_id, 'get_sessions' );
		$verifier = array_keys( $tokens )[0];

		$session_data = $this->_invoke_method(
			$user_id,
			'get_session',
			array(
				$verifier,
			)
		);

		$this->assertEquals(
			$session_data['expiration'],
			$expiration,
			'Failed to assert that session expiration is stored in Redis.'
		);
	}

	/**
	 * Test `update_session()` method.
	 *
	 * @covers ::update_session()
	 * @covers ::update_sessions()
	 * @return void
	 */
	public function test_update_session() {
		$user_id = $this->factory->user->create();
		$plugin  = new Plugin( $user_id );

		$plugin->create( time() + 60 );

		$sessions = $this->_invoke_method( $user_id, 'get_sessions' );
		$verifier = array_keys( $sessions )[0];

		$this->assertNotEmpty(
			$sessions,
			'Failed to assert that session was created.'
		);

		$this->_invoke_method(
			$user_id,
			'update_session',
			array(
				$verifier,
			)
		);

		$this->assertEmpty(
			$this->_invoke_method(
				$user_id,
				'get_session',
				array(
					$verifier,
				)
			),
			'Failed to assert that session is not destroyed when no session data is provided.'
		);

		$plugin->create( time() + 60 );

		$sessions = $this->_invoke_method( $user_id, 'get_sessions' );
		$verifier = array_keys( $sessions )[0];
		$session_data = array(
			'expiration' => time() + 60,
			'foo'        => 'bar',
		);

		$this->_invoke_method(
			$user_id,
			'update_session',
			array(
				$verifier,
				$session_data,
			)
		);

		$this->assertEquals(
			$session_data,
			$this->_invoke_method(
				$user_id,
				'get_session',
				array(
					$verifier,
				)
			),
			'Failed to assert that session is updated when session data is provided.'
		);
	}

	/**
	 * Test `destroy_other_sessions()` method.
	 *
	 * @covers ::destroy_other_sessions()
	 * @return void
	 */
	public function test_destroy_other_sessions() {
		$user_id = $this->factory->user->create();
		$plugin  = new Plugin( $user_id );

		$plugin->create( time() + 60 );
		$plugin->create( time() + 120 );
		$plugin->create( time() + 180 );

		$sessions = $this->_invoke_method( $user_id, 'get_sessions' );

		$this->assertCount(
			3,
			$sessions,
			'Failed to assert that multiple sessions were created.'
		);

		$verifier = array_keys( $sessions )[0];

		$this->_invoke_method(
			$user_id,
			'destroy_other_sessions',
			array(
				$verifier,
			)
		);

		$this->assertCount(
			1,
			$this->_invoke_method(
				$user_id,
				'get_sessions'
			),
			'Failed to assert that other sessions are destroyed.'
		);
	}

	/**
	 * Test `destroy_all_sessions()` method.
	 *
	 * @covers ::destroy_all_sessions()
	 * @return void
	 */
	public function test_destroy_all_sessions() {
		$user_id = $this->factory->user->create();
		$plugin  = new Plugin( $user_id );

		$plugin->create( time() + 60 );
		$plugin->create( time() + 120 );
		$plugin->create( time() + 180 );

		$sessions = $this->_invoke_method( $user_id, 'get_sessions' );

		$this->assertCount(
			3,
			$this->_invoke_method(
				$user_id,
				'get_sessions'
			),
			'Failed to assert that multiple sessions were created.'
		);

		$this->_invoke_method(
			$user_id,
			'destroy_all_sessions'
		);

		$this->assertEmpty(
			$this->_invoke_method(
				$user_id,
				'get_sessions'
			),
			'Failed to assert that all sessions were destroyed.'
		);
	}

	/**
	 * Test `drop_sessions()` method.
	 *
	 * @covers ::drop_sessions()
	 * @covers ::flush_redis_db()
	 * @return void
	 */
	public function test_drop_sessions() {
		$user_1        = $this->factory->user->create();
		$plugin_user_1 = new Plugin( $user_1 );
		$user_2        = $this->factory->user->create();
		$plugin_user_2 = new Plugin( $user_2 );

		$plugin_user_1->create( time() + 60 );
		$plugin_user_1->create( time() + 120 );
		$plugin_user_1->create( time() + 180 );
		$plugin_user_2->create( time() + 60 );
		$plugin_user_2->create( time() + 120 );
		$plugin_user_2->create( time() + 180 );

		$this->assertCount(
			3,
			$this->_invoke_method(
				$user_1,
				'get_sessions'
			),
			'Failed to assert that multiple sessions were created for user 1.'
		);

		$this->assertCount(
			3,
			$this->_invoke_method(
				$user_2,
				'get_sessions'
			),
			'Failed to assert that multiple sessions were created for user 2.'
		);

		$this->_invoke_method(
			$user_1,
			'flush_redis_db'
		);

		$this->assertEmpty(
			$this->_invoke_method(
				$user_1,
				'get_sessions'
			),
			'Failed to assert that sessions were destroyed for user 1.'
		);

		$this->assertEmpty(
			$this->_invoke_method(
				$user_2,
				'get_sessions'
			),
			'Failed to assert that sessions were destroyed for user 2.'
		);
	}

	/**
	 * Test `get_key()` method.
	 *
	 * @covers ::get_key()
	 * @return void
	 */
	public function test_get_key() {
		$user_id = $this->factory->user->create();
		$plugin  = new Plugin( $user_id );

		$this->assertEquals(
			$plugin->prefix . ':' . $user_id,
			$this->_invoke_method( $user_id, 'get_key' )
		);
	}

	/**
	 * Invoke a non-public class method.
	 *
	 * @param int    $user_id     WP User ID.
	 * @param string $method_name Method name.
	 * @param array  $args        Method arguments.
	 * @return mixed
	 */
	protected function _invoke_method(
		$user_id,
		$method_name,
		$args = array()
	) {
		$object     = new Plugin( $user_id );
		$reflection = new ReflectionClass( $object );
		$method     = $reflection->getMethod( $method_name );
		$method->setAccessible( true );

		return $method->invokeArgs( $object, $args );
	}

	/**
	 * Get value of non-public property.
	 *
	 * @param int    $user_id       WP User ID.
	 * @param string $property_name Property name.
	 * @return mixed
	 */
	protected function _get_property( $user_id, $property_name ) {
		$object     = new Plugin( $user_id );
		$reflection = new ReflectionClass( $object );
		$property   = $reflection->getProperty( $property_name );
		$property->setAccessible( true );

		return $property->getValue( $object );
	}
}

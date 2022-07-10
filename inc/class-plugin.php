<?php
/**
 * Offload session storage to Redis.
 *
 * @package WP_Redis_User_Session_Storage
 */

namespace Redis_User_Session_Storage;

use Redis;
use RedisException;
use WP_Session_Tokens;

/**
 * Redis-based user sessions token manager.
 *
 * @since 0.1
 */
class Plugin extends WP_Session_Tokens {
	/**
	 * Holds the Redis client.
	 *
	 * @var Redis
	 */
	private $redis;

	/**
	 * Track if Redis is available
	 *
	 * @var bool
	 */
	private $redis_connected = false;

	/**
	 * Prefix used to namespace keys
	 *
	 * @var string
	 */
	public $prefix = 'wpruss';

	/**
	 * Create Redis connection using the Redis PECL extension
	 *
	 * @param int $user_id User ID.
	 */
	public function __construct( $user_id ) {
		// General Redis settings.
		$redis = array(
			'host'       => '127.0.0.1',
			'port'       => 6379,
			'socket'     => null,
			'serializer' => Redis::SERIALIZER_PHP,
		);

		if ( defined( 'WP_REDIS_USER_SESSION_HOST' ) && WP_REDIS_USER_SESSION_HOST ) {
			$redis['host'] = WP_REDIS_USER_SESSION_HOST;
		}
		if ( defined( 'WP_REDIS_USER_SESSION_PORT' ) && WP_REDIS_USER_SESSION_PORT ) {
			$redis['port'] = WP_REDIS_USER_SESSION_PORT;
		}
		if ( defined( 'WP_REDIS_USER_SESSION_SOCKET' ) && WP_REDIS_USER_SESSION_SOCKET ) {
			$redis['socket'] = WP_REDIS_USER_SESSION_SOCKET;
		}
		if ( defined( 'WP_REDIS_USER_SESSION_AUTH' ) && WP_REDIS_USER_SESSION_AUTH ) {
			$redis['auth'] = WP_REDIS_USER_SESSION_AUTH;
		}
		if ( defined( 'WP_REDIS_USER_SESSION_DB' ) && WP_REDIS_USER_SESSION_DB ) {
			$redis['database'] = WP_REDIS_USER_SESSION_DB;
		}
		if ( defined( 'WP_REDIS_USER_SESSION_SERIALIZER' ) && WP_REDIS_USER_SESSION_SERIALIZER ) {
			$redis['serializer'] = WP_REDIS_USER_SESSION_SERIALIZER;
		}

		// Use Redis PECL library.
		try {
			$this->redis = new Redis();

			// Socket preferred, but TCP supported.
			if ( $redis['socket'] ) {
				$this->redis->connect( $redis['socket'] );
			} else {
				$this->redis->connect( $redis['host'], $redis['port'] );
			}

			$this->redis->setOption( Redis::OPT_SERIALIZER, $redis['serializer'] );

			if ( isset( $redis['auth'] ) ) {
				$this->redis->auth( $redis['auth'] );
			}

			if ( isset( $redis['database'] ) ) {
				$this->redis->select( $redis['database'] );
			}

			$this->redis_connected = true;
		} catch ( RedisException $e ) {
			$this->redis_connected = false;
		}

		// Pass user ID to parent.
		parent::__construct( $user_id );
	}

	/**
	 * Get all sessions of a user.
	 *
	 * @return array Sessions of a user.
	 */
	protected function get_sessions() {
		if ( ! $this->redis_connected ) {
			return array();
		}

		$key = $this->get_key();

		if ( ! $this->redis->exists( $key ) ) {
			return array();
		}

		$sessions = $this->redis->get( $key );
		if ( ! is_array( $sessions ) ) {
			return array();
		}

		$sessions = array_map( array( $this, 'prepare_session' ), $sessions );
		return array_filter( $sessions, array( $this, 'is_still_valid' ) );
	}

	/**
	 * Converts an expiration to an array of session information.
	 *
	 * @param mixed $session Session or expiration.
	 * @return array Session.
	 */
	protected function prepare_session( $session ) {
		if ( is_int( $session ) ) {
			return array( 'expiration' => $session );
		}

		return $session;
	}

	/**
	 * Retrieve a session by its verifier (token hash).
	 *
	 * @param string $verifier Verifier of the session to retrieve.
	 * @return array|null The session, or null if it does not exist
	 */
	protected function get_session( $verifier ) {
		$sessions = $this->get_sessions();

		if ( isset( $sessions[ $verifier ] ) ) {
			return $sessions[ $verifier ];
		}

		return null;
	}

	/**
	 * Update a session by its verifier.
	 *
	 * @param string $verifier Verifier of the session to update.
	 * @param array  $session  Optional. Session. Omitting this argument destroys the session.
	 */
	protected function update_session( $verifier, $session = null ) {
		$sessions = $this->get_sessions();

		if ( $session ) {
			$sessions[ $verifier ] = $session;
		} else {
			unset( $sessions[ $verifier ] );
		}

		$this->update_sessions( $sessions );
	}

	/**
	 * Update a user's sessions in Redis.
	 *
	 * @param array $sessions Sessions.
	 */
	protected function update_sessions( $sessions ) {
		if ( ! $this->redis_connected ) {
			return;
		}

		$key = $this->get_key();

		if ( $sessions ) {
			$this->redis->set( $key, $sessions );
		} elseif ( $this->redis->exists( $key ) ) {
			$this->redis->del( $key );
		}
	}

	/**
	 * Destroy all session tokens for a user, except a single session passed.
	 *
	 * @param string $verifier Verifier of the session to keep.
	 */
	protected function destroy_other_sessions( $verifier ) {
		$session = $this->get_session( $verifier );
		$this->update_sessions( array( $verifier => $session ) );
	}

	/**
	 * Destroy all session tokens for a user.
	 */
	protected function destroy_all_sessions() {
		$this->update_sessions( array() );
	}

	/**
	 * Destroy all session tokens for all users.
	 *
	 * @return bool
	 */
	public static function drop_sessions() {
		return static::get_instance( 0 )->flush_redis_db();
	}

	/**
	 * Empty database, clearing all tokens.
	 *
	 * @return bool
	 */
	protected function flush_redis_db() {
		return $this->redis->flushDB();
	}

	/**
	 * Build key for current user.
	 *
	 * @return string
	 */
	protected function get_key() {
		return $this->prefix . ':' . $this->user_id;
	}
}

<?php
/**
 * PHPUnit bootstrap file
 *
 * @package WP_Revisions_Control
 */

$redis_user_session_storage = getenv( 'WP_TESTS_DIR' );

if ( ! $redis_user_session_storage ) {
	$redis_user_session_storage = rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress-tests-lib';
}

if ( ! file_exists( $redis_user_session_storage . '/includes/functions.php' ) ) {
	echo "Could not find $redis_user_session_storage/includes/functions.php, have you run bin/install-wp-tests.sh ?" . PHP_EOL; // WPCS: XSS ok.
	exit( 1 );
}

// Give access to tests_add_filter() function.
require_once $redis_user_session_storage . '/includes/functions.php';

/**
 * Manually load the plugin being tested.
 */
function redis_user_session_storage_tests_manually_load_plugin() {
	require dirname( dirname( __FILE__ ) ) . '/redis-user-session-storage.php';
}
tests_add_filter( 'muplugins_loaded', 'redis_user_session_storage_tests_manually_load_plugin' );

// Start up the WP testing environment.
require $redis_user_session_storage . '/includes/bootstrap.php';

// Set Redis host for CI.
if ( ! defined( 'WP_REDIS_USER_SESSION_HOST' ) ) {
	define( 'WP_REDIS_USER_SESSION_HOST', 'redis' );
}

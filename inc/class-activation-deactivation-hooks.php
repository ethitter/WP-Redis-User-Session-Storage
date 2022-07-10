<?php
/**
 * Plugin activation hooks to migrate and clean up data.
 *
 * @package WP_Redis_User_Session_Storage
 */

namespace Redis_User_Session_Storage;

/**
 * Class Activation_Deactivation_Hooks.
 */
final class Activation_Deactivation_Hooks {
	/**
	 * Cron hook for usermeta session cleanup.
	 *
	 * @var string
	 */
	private $cron_hook = 'redis_user_session_storage_clean_usermeta_storage';

	/**
	 * Activation_Hooks constructor.
	 *
	 * @param string $plugin_file Path to plugin's main file.
	 */
	public function __construct( $plugin_file ) {
		register_activation_hook(
			$plugin_file,
			array( $this, 'clean_usermeta_storage' )
		);

		register_deactivation_hook(
			$plugin_file,
			array( $this, 'clean_redis_storage' )
		);

		add_action(
			$this->cron_hook,
			array( $this, 'do_scheduled_cleanup' )
		);
	}

	/**
	 * Remove all sessions from usermeta on activation.
	 *
	 * @return void
	 */
	public function clean_usermeta_storage() {
		wp_schedule_single_event( time() + 600, $this->cron_hook );
	}

	/**
	 * Remove session data from usermeta using cron to avoid excessive load.
	 *
	 * While this could use `WP_User_Meta_Session_Tokens::drop_sessions()`, this
	 * approach is safer for large sites.
	 *
	 * @return void
	 */
	public function do_scheduled_cleanup() {
		global $wpdb;

		$this->clean_usermeta_storage();

		$key = 'session_tokens';

		$count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM $wpdb->usermeta WHERE meta_key = %s",
				$key
			)
		);

		if ( ! $count ) {
			wp_clear_scheduled_hook( $this->cron_hook );
			return;
		}

		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM $wpdb->usermeta WHERE meta_key = %s LIMIT 500",
				$key
			)
		);
	}

	/**
	 * Remove all sessions from Redis on deactivation.
	 *
	 * @return void
	 */
	public function clean_redis_storage() {
		wp_clear_scheduled_hook( $this->cron_hook );

		Plugin::drop_sessions();
	}
}

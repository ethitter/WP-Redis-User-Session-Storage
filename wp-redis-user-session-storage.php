<?php
/**
 * Plugin Name: Redis User Session Storage
 * Plugin URI: https://ethitter.com/plugins/redis-user-session-storage/
 * Description: Store WordPress session tokens in Redis rather than the usermeta table. Requires the Redis PECL extension.
 * Version: 0.2
 * Author: Erick Hitter
 * Author URI: https://ethitter.com/
 * Text Domain: wp-redis-user-session-storage
 * Domain Path: /languages/
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @package WP_Redis_User_Session_Storage
 */

namespace Redis_User_Session_Storage;

use Redis;
use WP_Session_Tokens;

/**
 * Load plugin when safe to do so, accounting for previous plugin name.
 *
 * WordPress.org no longer accepts plugins beginning with the `WP` prefix, so
 * this was renamed to comply.
 *
 * @return void
 */
function load() {
	if (
		! class_exists( Redis::class, false )
		|| ! class_exists( WP_Session_Tokens::class, false )
	) {
		return;
	}

	require_once __DIR__ . '/inc/class-plugin.php';
	require_once __DIR__ . '/inc/class-activation-deactivation-hooks.php';

	// Alias plugin's original class name.
	class_alias(
		Plugin::class,
		'WP_Redis_User_Session_Storage',
		false
	);

	new Activation_Deactivation_Hooks( __FILE__ );

	add_action(
		'plugins_loaded',
		__NAMESPACE__ . '\action_plugins_loaded'
	);

	// Hooked at 9 in case old plugin is also active.
	add_filter(
		'session_token_manager',
		__NAMESPACE__ . '\set_session_token_manager',
		9
	);
}
load();

/**
 * Perform various actions after plugin loads.
 *
 * @return void
 */
function action_plugins_loaded() {
	load_plugin_textdomain(
		'wp-redis-user-session-storage',
		false,
		dirname( plugin_basename( __FILE__ ) ) . '/languages/'
	);
}

/**
 * Override Core's default usermeta-based token storage.
 *
 * @param string $manager Name of session-manager class.
 * @return string
 */
function set_session_token_manager( $manager ) {
	return Plugin::class;
}

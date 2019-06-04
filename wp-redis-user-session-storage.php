<?php
/**
 * Load plugin.
 *
 * @package WP_Redis_User_Session_Storage
 */

/**
 * Plugin Name: WP Redis User Session Storage
 * Plugin URI: https://ethitter.com/plugins/wp-redis-user-session-storage/
 * Description: Store WordPress session tokens in Redis rather than the usermeta table. Requires the Redis PECL extension.
 * Version: 0.1
 * Author: Erick Hitter
 * Author URI: https://ethitter.com/
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
 */

require_once __DIR__ . '/inc/class-wp-redis-user-session-storage.php';

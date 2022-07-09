# Redis User Session Storage #
**Contributors:** [ethitter](https://profiles.wordpress.org/ethitter/)  
**Donate link:** https://ethitter.com/donate/  
**Tags:** user sessions, session tokens, session storage  
**Requires at least:** 4.0  
**Tested up to:** 6.0  
**Stable tag:** 0.2  
**Requires PHP:** 5.6  
**License:** GPLv2 or later  
**License URI:** http://www.gnu.org/licenses/gpl-2.0.html  

Store WordPress session tokens in Redis rather than the usermeta table.

## Description ##

Store WordPress user session tokens in Redis rather than the usermeta table.

This plugin was previously known as `WP Redis User Session Storage` and was renamed to comply with WordPress.org naming constraints.

## Installation ##

1. Install and configure Redis.
2. Install the [Redis PECL module](http://pecl.php.net/package/redis).
3. Activate the plugin network-wide or by placing it in `mu-plugins`.
4. By default, the script will connect to Redis at `127.0.0.1:6379`. See the *Connecting to Redis* section for further options.

## Frequently Asked Questions ##

### Connecting to Redis ###
By default, the plugin uses `127.0.0.1` and `6379` as the default host and port, respectively, when creating a new client instance; the default database of `0` is also used.

Specify any of the following constants to set the necessary, non-default connection values for your Redis instance:

* `WP_REDIS_USER_SESSION_HOST` - Hostname or IP of the Redis server, defaults to `127.0.0.1`.
* `WP_REDIS_USER_SESSION_PORT` - Port of the Redis server, defaults to `6379`.
* `WP_REDIS_USER_SESSION_SOCKET` - Path to a Unix socket file for the Redis server, if available. Takes precedence over the port value when set.
* `WP_REDIS_USER_SESSION_AUTH` - Password for the Redis server, if required.
* `WP_REDIS_USER_SESSION_DB` - Database number to use for the Redis server, defaults to `0`.
* `WP_REDIS_USER_SESSION_SERIALIZER` - Serializer to use for the Redis server, defaults to `Redis::SERIALIZER_PHP`.

### How do I upgrade from WP Redis User Session Storage? ###

Install and activate this plugin, then deactivate the old plugin. Both plugins can safely be activated together as long as no additional classes extend the `WP_Redis_User_Session_Storage` class. After activating this plugin, deactivate the `WP Redis User Session Storage` plugin and remove it.

## Changelog ##

### 0.2 ###
* Rename plugin to `Redis User Session Storage` to comply with WordPress.org plugin-naming requirements.
* Allow two versions of this plugin to co-exist safely to support seamless migration.
* Changes plugin class name to `Redis_User_Session_Storage` from `WP_Redis_User_Session_Storage`.

### 0.1 ###
* Initial public release

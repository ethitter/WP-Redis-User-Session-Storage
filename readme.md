## Overview

Store WordPress user session tokens in Redis rather than the usermeta table. Requires PECL Redis library.

Requires WordPress 4.0, which should be released soon but isn’t currently suitable for production sites. In the meantime, **everything** is subject to change.

## Authors

* Erick Hitter

## Installation
1. Install and configure Redis. There is a good tutorial [here](http://www.saltwebsites.com/2012/install-redis-245-service-centos-6).
2. Install the [Redis PECL module](http://pecl.php.net/package/redis).
3. Activate the plugin network-wide or by placing it in `mu-plugins`.
4. By default, the script will connect to Redis at 127.0.0.1:6379. See the *Connecting to Redis* section for further options.

### Connecting to Redis ###

By default, the plugin uses `127.0.0.1` and `6379` as the default host and port when creating a new client instance; the default database of `0` is also used. Three constants are provided to override these default values.

Specify `WP_REDIS_USER_SESSION_HOST`, `WP_REDIS_USER_SESSION_PORT`, and `WP_REDIS_USER_SESSION_DB` to set the necessary, non-default connection values for your Redis instance.

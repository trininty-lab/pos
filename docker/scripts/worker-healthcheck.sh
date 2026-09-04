#!/bin/sh
set -eu

case "$(tr '\000' ' ' </proc/1/cmdline)" in
	*"artisan queue:work"*) ;;
	*) exit 1 ;;
esac

php -r '$redis = new Redis(); $redis->connect(getenv("REDIS_HOST") ?: "redis", (int) (getenv("REDIS_PORT") ?: 6379)); exit($redis->ping() === true ? 0 : 1);'

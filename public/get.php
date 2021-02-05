<?php

$redis = new Redis();

$redis_conf = parse_url(getenv('REDIS_URL'));

$redis->connect($redis_conf['host'], $redis_conf['port']);

$redis->auth($redis_conf['pass']);

echo $redis->get('key');

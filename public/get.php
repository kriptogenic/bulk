<?php

$redis = new Redis();

$redis->connect(getenv('REDIS_URL'));

echo $redis->get('key');

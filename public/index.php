<?php


//echo "Hello world";

$p = $_GET['sa'] ?? null;

$redis = new Redis();

$redis_conf = parse_url(getenv('REDIS_URL'));

var_dump($redis->connect($redis_conf['host'], $redis_conf['port']));

$redis->auth($redis_conf['pass']);

var_dump($redis->set('key', $p));
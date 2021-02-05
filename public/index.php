<?php


echo "Hello world";

$p = $_GET['sa'] ?? null;

$redis = new Redis();

$redis->connect(getenv('REDIS_URL'));

var_dump($redis->set('key', $p));
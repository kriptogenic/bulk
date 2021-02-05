<?php


//echo "Hello world";

$p = $_GET['sa'] ?? null;

$redis = new Redis();

var_dump($redis->connect(getenv('REDIS_URL')));

var_dump($redis->set('key', $p));
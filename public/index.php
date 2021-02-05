<?php


echo "Hello world";

$p = $_GET['sa'] ?? null;

$redis = new Redis();

$redis->append('key', $p);
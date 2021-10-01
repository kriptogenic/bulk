<?php

$red = new Redis();

$r = $red->connect('172.26.48.1', 6379);

var_dump(SWOOLE_VERSION);
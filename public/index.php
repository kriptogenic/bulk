<?php

const BASE_DIR = __DIR__ . '/../';
require BASE_DIR . 'vendor/autoload.php';

$app = new \App\Http\App();

$app->run();
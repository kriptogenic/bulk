<?php

$http = new Swoole\HTTP\Server("0.0.0.0", getenv('PORT'));

$http->on('request', function (\Swoole\Http\Request $request, \Swoole\Http\Response $response) {
    $data = json_decode($request->getContent());
    if ($data->chat_id % 3) {

        $response->setStatusCode(429);
    }
//    echo $request->getData();
    var_dump($request);
    $response->end("<h1>Hello World. </h1>");

});

$http->start();

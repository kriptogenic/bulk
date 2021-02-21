<?php
define('TOKEN', getenv('TEST_TOKEN'));
\Swoole\Timer::tick(1000 * 60  * 5, function (){
    $s = curl_init();
    curl_setopt_array($s, [
            CURLOPT_URL => 'https://api.telegram.org/bot' . TOKEN . '/' . 'sendMessage',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_HEADER => false,
            CURLOPT_CONNECTTIMEOUT => 3,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_HTTPHEADER => [
                'Keep-Alive: timeout=5, max=1000',
                'Content-Type: application/json'
            ],
            CURLOPT_POSTFIELDS => json_encode(['chat_id' => 47543915, 'text' => date('H:i:s')])
        ]
    );
    curl_exec($s);
});

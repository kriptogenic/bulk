<?php

declare(strict_types=1);

namespace App\Memory;

use InvalidArgumentException;
use Redis as PHPRedis;

class Redis
{
    private string $host;
    private int $port;
    private ?string $password;

    private PHPRedis $connection;

    public function __construct(string $connection_url)
    {
        $connection_config = parse_url($connection_url);

        if (!is_array($connection_config)) {
            throw new InvalidArgumentException('Wrong connection url');
        }

        if (empty($connection_config['host'])) {
            throw new InvalidArgumentException('Connection host is required');
        }
        $this->host = $connection_config['host'];

        if (empty($connection_config['port'])) {
            throw new InvalidArgumentException('Connection port is required');
        }
        $this->port = $connection_config['port'];

        // Creating connection
        $this->connection = new PHPRedis();
        $this->connection->connect($this->host, $this->port);

        if (!empty($connection_config['pass'])) {
            $this->password = $connection_config['pass'];
            $this->connection->auth($this->password);
        }
    }

    public function setData(int $bot_id, string $token, string $method, array $data, array $chats_id)
    {
        $this->connection->hMSet('request_data:' . $bot_id, $data);

        $this->connection->hMSet('bot_data:' . $bot_id, [
            'token' => $token,
            'method' => $method
        ]);
        $this->connection->sAdd('chats_id:' . $bot_id, ...$chats_id);

        $this->connection->sAdd('pending', $bot_id);
    }

    public function taskExists(int $bot_id)
    {
        return $this->connection->sIsMember('pending', $bot_id)
            || $this->connection->sIsMember('processing', $bot_id);
    }

    public function delData(int $bot_id)
    {
        $this->connection->del('request_data:' . $bot_id,
            'bot_data:' . $bot_id, 'chats_id:' . $bot_id);
        $this->connection->sRem('pending', $bot_id);
        $this->connection->sRem('processing', $bot_id);
    }
}

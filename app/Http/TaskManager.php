<?php

declare(strict_types=1);

namespace App\Http;

use InvalidArgumentException;
use Redis as PHPRedis;

class TaskManager
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

    public function add(int $bot_id, string $token, string $method, int $bulk_size, array $data, array $chats_id)
    {
        $this->connection->hMSet('request_data:' . $bot_id, $data);

        $this->connection->hMSet('bot_data:' . $bot_id, [
            'token' => $token,
            'method' => $method,
            'bulk_size' => $bulk_size
        ]);
        $this->connection->sAdd('chats_id:' . $bot_id, ...$chats_id);

        $this->connection->zAdd('pending', time(), $bot_id);
    }

    public function exists(int $bot_id): bool
    {
        if ($this->connection->zScore('pending', $bot_id) !== false) {
            return true;
        }

        if ($this->connection->zScore('processing', $bot_id) !== false) {
            return true;
        }

        return false;
    }

    public function remove(int $bot_id)
    {
        $this->connection->del('request_data:' . $bot_id,
            'bot_data:' . $bot_id, 'chats_id:' . $bot_id);
        $this->connection->zRem('pending', $bot_id);
        $this->connection->zRem('processing', $bot_id);
    }
}

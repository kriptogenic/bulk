<?php

declare(strict_types=1);

namespace App\Async\Memory;

use InvalidArgumentException;
use Redis;
use Swoole\Coroutine\Channel;

class RedisConnectionPool
{
    private Channel $pool;

    private string $host;

    private int $port;

    private ?string $password;

    public function __construct(string $connection_url, int $size)
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

        if ($size < 1) {
            throw new InvalidArgumentException('Pool size must be at least 1');
        }

        $this->password = $connection_config['pass'] ?? null;

        $this->pool = new Channel($size);

        while ($size--) {
            $this->pool->push($this->createConnection($this->host, $this->port, $this->password));
        }
    }

    public function get(): Redis
    {
        return $this->pool->pop();
    }

    public function put(Redis $conection)
    {
        $this->pool->push($conection);
    }

    private function createConnection(string $host, int $port, string $password = null): Redis
    {
        $connection = new Redis();
        $connection->connect($host, $port);

        if (!is_null($password)) {
            $connection->auth($password);
        }

        return $connection;
    }
}

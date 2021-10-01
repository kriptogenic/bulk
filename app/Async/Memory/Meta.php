<?php

declare(strict_types=1);

namespace App\Async\Memory;

/**
 * Class Meta
 * ValueObject
 */
class Meta
{
    public int $bulkSize;
    public string $token;
    public string $method;

    public function __construct(array $meta)
    {
        $this->bulkSize = intval($meta['bulk_size']);
        $this->method = $meta['method'];
        $this->token = $meta['token'];
    }
}

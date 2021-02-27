<?php

declare(strict_types=1);

namespace App\Http;

use Cekta\DI\ArrayCache;
use Cekta\DI\InfiniteRecursionDetector;
use Cekta\DI\Reflection;
use Cekta\DI\Strategy;
use Cekta\DI\Strategy\Autowiring;
use Cekta\DI\Strategy\Definition;
use Cekta\DI\Strategy\Implementation;
use Cekta\DI\Strategy\KeyValue;
use Psr\Container\ContainerInterface;

class Container implements ContainerInterface
{
    private ContainerInterface $container;

    /**
     * @param array<string, mixed> $params
     * @param array<string, string> $implementations
     * @param array<string, mixed> $definitions
     */
    public function __construct(array $params = [], array $implementations = [], array $definitions = [])
    {
        $containers = [
            new KeyValue($params),
            new Implementation($implementations, $this),
            new Definition($definitions, $this),
            new Autowiring(new Reflection(), $this),
        ];
        $this->container = new ArrayCache(new InfiniteRecursionDetector(new Strategy(...$containers)));
    }

    public function get($id): mixed
    {
        return $this->container->get($id);
    }

    public function has($id): bool
    {
        return $this->container->has($id);
    }
}

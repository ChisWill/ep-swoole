<?php

declare(strict_types=1);

namespace Ep\Tests\App\Component;

use Ep;
use RuntimeException;
use Yiisoft\Factory\Factory;

final class Pool
{
    private array $instances = [];

    private array $difinitions;
    private array $sizes;
    private array $nums;
    private Factory $factory;

    public function __construct(array $difinitions, array $sizes)
    {
        $this->difinitions = $difinitions;
        $this->sizes = $sizes;
        $this->nums = array_combine(array_keys($sizes), array_fill(0, count($sizes), 0));
        $this->instances = array_combine(array_keys($sizes), array_fill(0, count($sizes), []));
        $this->factory = Ep::getFactory();
    }

    public function get(string $id): object
    {
        if ($this->nums[$id] > 0) {
            return $this->getInstance($id);
        }

        if ($this->nums[$id] > $this->sizes[$id]) {
            throw new RuntimeException('Overload');
        }

        return $this->createInstance($id);
    }

    public function put(string $id, object $instance): void
    {
        $this->putInstance($id, $instance);
    }

    public function count(string $id): int
    {
        return count($this->instances[$id]);
    }

    private function createInstance(string $id): object
    {
        $this->nums[$id]++;
        return $this->factory->create($this->difinitions[$id]);
    }

    private function getInstance(string $id): object
    {
        $this->nums[$id]--;
        return array_pop($this->instances[$id]);
    }

    private function putInstance(string $id, object $instance): void
    {
        $this->nums[$id]++;
        $this->instances[$id][] = $instance;
    }
}

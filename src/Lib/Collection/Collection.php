<?php

namespace Resursbank\Ecom\Lib\Collection;

use ArrayAccess;
use Iterator;
use Countable;

/**
 * Base collection class
 */
class Collection implements ArrayAccess, Iterator, Countable
{
    protected ?string $type = null;
    private array $data;
    private int $position;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->position = 0;
    }

    public function count(): int
    {
        return count($this->data);
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function toArray(): array
    {
        return $this->data;
    }

    public function offsetSet($offset, $value): void
    {
        if (is_null($offset)) {
            $this->data[] = $value;
        } else {
            $this->data[$offset] = $value;
        }
    }

    public function offsetExists($offset): bool
    {
        return isset($this->data[$offset]);
    }

    public function offsetUnset($offset): void
    {
        unset($this->data[$offset]);
    }

    public function offsetGet($offset): mixed
    {
        if (!isset($this->data[$offset])) {
            $this->data[$offset] = null;
        }

        return $this->data[$offset];
    }

    public function rewind(): void
    {
        $this->position = 0;
    }

    public function current(): mixed
    {
        return $this->data[$this->position];
    }

    public function key(): mixed
    {
        return $this->position;
    }

    public function next(): void
    {
        ++$this->position;
    }

    public function valid(): bool
    {
        return isset($this->data[$this->position]);
    }
}


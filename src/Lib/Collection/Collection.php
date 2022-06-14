<?php

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Collection;

use ArrayAccess;
use Iterator;
use Countable;
use Resursbank\Ecom\Exception\TypeException;

/**
 * Base collection class
 */
class Collection implements ArrayAccess, Iterator, Countable
{
    private const TYPE_ERR = "Collection requires data to be of type %s, received %s";
    // @todo: Enforce type when adding data, need to drop this again for presta stuff...

    protected string $type;
    private array $data;
    private int $position;

    /**
     * @param array $data
     * @param string|null $type
     * @throws TypeException
     */
    public function __construct(array $data, string $type = null)
    {
        $this->verifyDataArrayType($data, $type);
        $this->data = $data;
        $this->type = $type;
        $this->position = 0;
    }

    /**
     * Verify the type of objects in collection data
     *
     * @param array $data
     * @param string $type
     * @return void
     * @throws TypeException
     */
    private function verifyDataArrayType(array $data, string $type): void
    {
        foreach ($data as $item) {
            if (
                (is_object($item) && $item::class !== $type) ||
                (!is_object($item) && gettype($item) !== $type)
            ) {
                throw new TypeException(
                    sprintf(
                        self::TYPE_ERR,
                        $type,
                        (is_object($item) ? $item::class : gettype($item))
                    )
                );
            }
        }
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

    /**
     * @param mixed $offset
     * @param mixed $value
     * @return void
     * @throws TypeException
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        if (!($value instanceof $this->type)) {
            throw new TypeException(sprintf(self::TYPE_ERR, $this->type, $value::class));
        }

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

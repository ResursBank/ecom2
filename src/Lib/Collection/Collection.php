<?php

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Collection;

use ArrayAccess;
use Iterator;
use Countable;
use Resursbank\Ecom\Exception\TypeException;

/**
 * Base collection class
 *
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class Collection implements ArrayAccess, Iterator, Countable
{
    private const TYPE_ERR = "Collection requires data to be of type %s, received %s";
    private const TYPE_ERR_NO_DATA = "No type or data specified";

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
        $type = $this->determineType($data, $type);
        $this->verifyDataArrayType($data, $type);
        $this->data = $data;
        $this->type = $type;
        $this->position = 0;
    }

    /**
     * Get collection from specified type or first element of data array
     *
     * @param array $data
     * @param string|null $type
     * @return string
     * @throws TypeException
     */
    private function determineType(array $data, string $type = null): string
    {
        if ($type) {
            return $type;
        }

        if (!empty($data) && isset($data[0])) {
            return is_object($data[0]) ? $data[0]::class : gettype($data[0]);
        }

        throw new TypeException(message: self::TYPE_ERR_NO_DATA);
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
                    message: sprintf(
                        self::TYPE_ERR,
                        $type,
                        (is_object($item) ? $item::class : gettype($item))
                    )
                );
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function count(): int
    {
        return count($this->data);
    }

    /**
     * Get collection type
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Get data array from collection
     *
     * @return array
     */
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
        if (
            (is_object($value) && $value::class !== $this->type) ||
            (!is_object($value) && gettype($value) !== $this->type)
        ) {
            throw new TypeException(
                message: sprintf(
                    self::TYPE_ERR,
                    $this->type,
                    is_object($value) ? $value::class : gettype($value)
                )
            );
        }

        if (is_null($offset)) {
            $this->data[] = $value;
        } else {
            $this->data[$offset] = $value;
        }
    }

    /**
     * @inheritDoc
     */
    public function offsetExists($offset): bool
    {
        return isset($this->data[$offset]);
    }

    /**
     * @inheritDoc
     */
    public function offsetUnset($offset): void
    {
        unset($this->data[$offset]);
    }

    /**
     * @inheritDoc
     */
    public function offsetGet($offset): mixed
    {
        if (!isset($this->data[$offset])) {
            $this->data[$offset] = null;
        }

        return $this->data[$offset];
    }

    /**
     * @inheritDoc
     */
    public function rewind(): void
    {
        $this->position = 0;
    }

    /**
     * @inheritDoc
     */
    public function current(): mixed
    {
        return $this->data[$this->position];
    }

    /**
     * @inheritDoc
     */
    public function key(): mixed
    {
        return $this->position;
    }

    /**
     * @inheritDoc
     */
    public function next(): void
    {
        ++$this->position;
    }

    /**
     * @inheritDoc
     */
    public function valid(): bool
    {
        return isset($this->data[$this->position]);
    }
}

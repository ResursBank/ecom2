<?php

declare(strict_types=1);

namespace Resursbank\EcomTest\Lib\Collection;

use Exception;
use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Exception\TypeException;
use Resursbank\Ecom\Lib\Collection\Collection;

/**
 * Verifies that the Collection class works as intended.
 *
 * @psalm-suppress PropertyNotSetInConstructor
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
final class CollectionTest extends TestCase
{
    private array $data;

    /**
     * Set up data variable
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->data = [
            'foo',
            'bar',
            'baz',
            'baf'
        ];
    }

    /**
     * Verify that creation of Collection works
     *
     * @return void
     * @throws TypeException
     */
    public function testCreateCollection(): void
    {
        $collection = new Collection(data: $this->data, type: "string");

        $this::assertEquals(
            expected: Collection::class,
            actual: $collection::class
        );
    }

    /**
     * Verify that type verification works
     *
     * @return void
     */
    public function testCollectionTypeVerification(): void
    {
        $data = [
            'foo',
            42,
            'bar'
        ];

        $className = false;
        try {
            new Collection(data: $data, type: "string");
        } catch (Exception $e) {
            $className = get_class(object: $e);
        }

        $this::assertSame(expected: TypeException::class, actual: $className);
    }

    /**
     * Verify that it's impossible to add an item of the wrong type to a collection
     *
     * @return void
     * @throws TypeException
     */
    public function testAddWrongTypeData(): void
    {
        $collection = new Collection(data: $this->data);

        $className = false;
        try {
            $collection[] = 42;
        } catch (Exception $e) {
            $className = get_class(object: $e);
        }

        $this::assertSame(
            expected: TypeException::class,
            actual: $className
        );
        $this::assertEquals(
            expected: gettype(value: $this->data[0]),
            actual: $collection->getType()
        );
    }

    /**
     * Verify that the toArray method works
     *
     * @return void
     * @throws TypeException
     */
    public function testToArray(): void
    {
        $collection = new Collection(data: $this->data);
        $this::assertSame(
            expected: $this->data,
            actual: $collection->toArray()
        );
    }

    /**
     * Verify that type determination called in the Collection constructor works
     *
     * @return void
     * @throws TypeException
     */
    public function testTypeDetermination(): void
    {
        $collection = new Collection(data: $this->data);
        $type = gettype($this->data[0]);

        $this::assertEquals(
            expected: $type,
            actual: $collection->getType()
        );
    }

    /**
     * Verify that the count method works
     *
     * @return void
     * @throws TypeException
     */
    public function testCount(): void
    {
        $collection = new Collection(data: $this->data);

        $this::assertEquals(
            expected: count($this->data),
            actual: $collection->count()
        );
    }

    /**
     * Verify that the offsetSet method works
     *
     * @return void
     * @throws TypeException
     */
    public function testOffsetSet(): void
    {
        $data = ['foo'];

        $collection = new Collection(data: $data);
        $collection[1] = "bar";

        $this::assertEquals(
            expected: "bar",
            actual: $collection[1]
        );
    }

    /**
     * Verify that the offsetExists method works
     *
     * @return void
     * @throws TypeException
     */
    public function testOffsetExists(): void
    {
        $collection = new Collection(data: $this->data);

        $this::assertTrue(condition: $collection->offsetExists(offset: 1));
    }

    /**
     * Verify that the offsetUnset method works
     *
     * @return void
     * @throws TypeException
     */
    public function testOffsetUnset(): void
    {
        $collection = new Collection(data: $this->data);
        unset($collection[1]);

        $this::assertEmpty(actual: $collection[1]);
    }

    /**
     * Verify that the offsetGet method works
     *
     * @return void
     * @throws TypeException
     */
    public function testOffsetGet(): void
    {
        $collection = new Collection(data: $this->data);

        $this::assertEquals(
            expected: $this->data[1],
            actual: $collection->offsetGet(offset: 1)
        );
    }

    /**
     * Verify that the rewind method works
     *
     * @return void
     * @throws TypeException
     */
    public function testRewind(): void
    {
        $collection = new Collection(data: $this->data);
        $collection->next();
        $collection->next();
        $collection->rewind();

        $this::assertEquals(
            expected: 0,
            actual: $collection->key()
        );
    }

    /**
     * Verify that the current method works
     *
     * @return void
     * @throws TypeException
     */
    public function testCurrent(): void
    {
        $collection = new Collection(data: $this->data);
        $collection->next();

        $this::assertEquals(
            expected: $this->data[1],
            actual: $collection->current()
        );
    }

    /**
     * Verify that the key method works
     *
     * @return void
     * @throws TypeException
     */
    public function testKey(): void
    {
        $collection = new Collection(data: $this->data);
        $collection->next();

        $this::assertEquals(
            expected: 1,
            actual: $collection->key()
        );
    }

    /**
     * Verify that the next method works
     *
     * @return void
     * @throws TypeException
     */
    public function testNext(): void
    {
        $collection = new Collection(data: $this->data);
        $originalKey = $collection->key();
        $collection->next();

        $this::assertEquals(
            expected: 0,
            actual: $originalKey
        );
        $this::assertEquals(
            expected: 1,
            actual: $collection->key()
        );
    }

    /**
     * Verify that the valid method works
     *
     * @return void
     * @throws TypeException
     */
    public function testValid(): void
    {
        $collection = new Collection(data: $this->data);
        $shouldBeValid = $collection->valid();
        $maxIndex = count(value: $this->data) - 1;
        for ($i = 0; $i <= $maxIndex; $i++) {
            $collection->next();
        }
        $shouldBeInvalid = $collection->valid();

        $this::assertTrue(condition: $shouldBeValid);
        $this::assertNotTrue(condition: $shouldBeInvalid);
    }
}

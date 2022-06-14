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
 */
final class CollectionTest extends TestCase
{
    /**
     * Verify that creation of Collection works
     *
     * @return void
     * @throws TypeException
     */
    public function testCreateCollection(): void
    {
        $data = [
            'foo',
            'bar',
            'baz'
        ];
        $collection = new Collection(data: $data, type: "string");

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

        $this->assertSame(expected: TypeException::class, actual: $className);
    }

    /**
     * Verify that the toArray method works
     *
     * @return void
     * @throws TypeException
     */
    public function testToArray(): void
    {
        $data = [
            'foo',
            'bar',
            'baz'
        ];
        $collection = new Collection(data: $data, type: 'string');
        $this->assertSame(expected: $data, actual: $collection->toArray());
    }
}

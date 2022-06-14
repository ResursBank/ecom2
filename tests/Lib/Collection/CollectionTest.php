<?php

declare(strict_types=1);

namespace Resursbank\EcomTest\Lib\Collection;

use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Exception\TypeException;
use Resursbank\Ecom\Lib\Collection\Collection;

final class CollectionTest extends TestCase
{
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
     * 
     * 
     * @return void
     * @throws TypeException
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
            $collection = new Collection(data: $data, type: "string");
        } catch (\Exception $e) {
            $className = get_class(object: $e);
        }

        $this->assertSame(expected: TypeException::class, actual: $className);
    }

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

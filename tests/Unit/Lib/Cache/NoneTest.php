<?php

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Lib\Cache;

use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Cache\None;

/**
 * Assert the None cache driver works as expected.
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
class NoneTest extends TestCase
{
    /**
     * @var None
     */
    private None $cache;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->cache = new None();

        parent::setUp();
    }

    /**
     * Assert that method read() throws instance of ValidationException if our
     * key contains illegal characters.
     *
     * @return void
     */
    public function testReadThrowsWithIllegalKeyCharacter(): void
    {
        $this->expectException(exception: ValidationException::class);
        $this->cache->read(key: 'Wonky?');
    }

    /**
     * Assert ValidationException occurs when calling read() with an empty key.
     *
     * @return void
     */
    public function testReadThrowsWithEmptyKey(): void
    {
        $this->expectException(exception: ValidationException::class);
        $this->cache->read(key: '');
    }

    /**
     * Assert that read() method will always return null.
     *
     * @return void
     * @throws ValidationException
     */
    public function testReadReturnsNull(): void
    {
        self::assertNull(actual: $this->cache->read(key: 'whatever'));
    }

    /**
     * Assert that method write() throws instance of ValidationException if our
     * key contains illegal characters.
     *
     * @return void
     */
    public function testWriteThrowsWithIllegalKeyCharacter(): void
    {
        $this->expectException(exception: ValidationException::class);
        $this->cache->write(
            key: 'Illegal?',
            data: 'Potato plats grow in June',
            ttl: 12556
        );
    }

    /**
     * Assert ValidationException occurs when calling write() with an empty key.
     *
     * @return void
     */
    public function testWriteThrowsWithEmptyKey(): void
    {
        $this->expectException(exception: ValidationException::class);
        $this->cache->write(key: '', data: 'Stutter hat', ttl: 8723847);
    }

    /**
     * Assert that write() method is callable but doesn't do anything.
     *
     * @return void
     * @throws ValidationException
     */
    public function testWriteDoesNothing(): void
    {
        $this->cache->write(key: 'ThatKey', data: 'anything', ttl: 9999);
        $this->expectNotToPerformAssertions();
    }

    /**
     * Assert that method clear() throws instance of ValidationException if our
     * key contains illegal characters.
     *
     * @return void
     */
    public function testClearThrowsWithIllegalKeyCharacter(): void
    {
        $this->expectException(exception: ValidationException::class);
        $this->cache->clear(key: 'Rad!');
    }

    /**
     * Assert ValidationException occurs when calling clear() with an empty key.
     *
     * @return void
     */
    public function testClearThrowsWithEmptyKey(): void
    {
        $this->expectException(exception: ValidationException::class);
        $this->cache->clear(key: '');
    }

    /**
     * Assert that clear() method is callable but doesn't do anything.
     *
     * @return void
     * @throws ValidationException
     */
    public function testClearDoesNothing(): void
    {
        $this->cache->clear(key: 'SomeKey');
        $this->expectNotToPerformAssertions();
    }
}

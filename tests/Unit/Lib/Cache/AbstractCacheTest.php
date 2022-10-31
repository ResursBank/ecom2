<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Lib\Cache;

use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Cache\AbstractCache;
use Exception;

/**
 * This class will test general cache methods.
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
class AbstractCacheTest extends TestCase
{
    /**
     * Test unique instance of AbstractCache class (mocked).
     *
     * @var AbstractCache
     */
    private AbstractCache $cache;

    /**
     * Test unique cache key.
     *
     * @var string
     */
    private string $key;

    /**
     * @return void
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->cache = $this->getMockForAbstractClass(
            originalClassName: AbstractCache::class
        );

        $this->key = $this->getKey();

        parent::setUp();
    }

    /**
     * @return string
     * @throws Exception
     */
    private function getKey(): string
    {
        return (
            AbstractCache::CACHE_KEY_PREFIX .
            'test' .
            random_int(min: 0, max: 999999)
        );
    }

    /**
     * Assert that a key containing a mixture of upper-, lowercase, hyphens and
     * underscores pass validation.
     *
     * @return void
     * @throws ValidationException
     */
    public function testValidationPass(): void
    {
        $this->cache->validateKey(key: $this->key);
        $this->expectNotToPerformAssertions();
    }

    /**
     * Assert that keys containing illegal chars will cause ValidationException.
     *
     * @return void
     * @throws ValidationException
     */
    public function testValidationFailsWithIllegalChars(): void
    {
        $this->expectException(exception: ValidationException::class);
        $this->cache->validateKey(key: "$this->key!!");
    }

    /**
     * Assert that empty keys will cause ValidationException.
     *
     * @return void
     * @throws ValidationException
     */
    public function testValidationFailsWithEmpty(): void
    {
        $this->expectException(exception: ValidationException::class);
        $this->cache->validateKey(key: '');
    }

    /**
     * Assert that empty keys will cause ValidationException.
     *
     * @return void
     * @throws ValidationException
     */
    public function testValidationFailsWithoutPrefix(): void
    {
        $this->expectException(exception: ValidationException::class);
        $this->cache->validateKey(key: 'some-key');
    }

    /**
     * Assert the getKey() method results in a prefixed cache key.
     *
     * @return void
     */
    public function testGetKeyReturnsPrefixedKey(): void
    {
        $this->assertSame(
            expected: AbstractCache::CACHE_KEY_PREFIX . 'test-key',
            actual: AbstractCache::getKey(key: 'test-key')
        );
    }
}

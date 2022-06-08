<?php

declare(strict_types=1);

namespace Resursbank\EcomTest\Lib\Cache;

use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Cache\AbstractCache;

/**
 * This class will test general cache methods.
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
class AbstractCacheTest extends TestCase
{
    private AbstractCache $cache;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->cache = $this->getMockForAbstractClass(
            originalClassName: AbstractCache::class
        );

        parent::setUp();
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
        $this->cache->validateKey(key: 'yAd4-Ba55_t35ST');
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
        $this->cache->validateKey(key: 'Yam!');
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
}

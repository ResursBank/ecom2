<?php

declare(strict_types=1);

namespace Resursbank\EcomTest\Lib\Cache;

use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Cache\AbstractCache;

/**
 * This class will test general cache methods.
 */
class AbstractCacheTest extends TestCase
{
    private AbstractCache $cache;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->cache = $this->getMockForAbstractClass(AbstractCache::class);
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
        $this->cache->validateKey('yAd4-Ba55_t35ST');
        $this->expectNotToPerformAssertions();
    }

    /**
     * Assert that keys containing an illegal character will cause an Exception.
     *
     * @return void
     * @throws ValidationException
     */
    public function testValidationFails(): void
    {
        $this->expectException(ValidationException::class);
        $this->cache->validateKey('Yam!');
    }
}
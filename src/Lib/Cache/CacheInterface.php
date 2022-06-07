<?php

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Cache;

use Resursbank\Ecom\Lib\Cache\Model\Data;

/**
 * Describes methods required for a cache storage driver.
 */
interface CacheInterface
{
    /**
     * Read value from cache. NULL means there was no valid value
     *
     * @param string $key
     * @return Data|null
     */
    public function read(string $key): ?Data;

    /**
     * Write value to cache.
     *
     * @param string $key
     * @param string $data
     * @param int $ttl
     * @return void
     */
    public function write(string $key, string $data, int $ttl): void;

    /**
     * Clear value from cache manually.
     *
     * @param string $key
     * @return void
     */
    public function clear(string $key): void;

    /**
     * Validate key.
     *
     * @param string $key
     * @return void
     */
    public function validateKey(string $key): void;
}

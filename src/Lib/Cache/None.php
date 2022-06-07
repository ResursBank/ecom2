<?php

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Cache;

/**
 * This class lets you avoid caching completely.
 */
class None implements CacheInterface
{
    /**
     * @inheritdoc
     */
    public function read(string $key): ?string
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function write(string $key, string $data, int $ttl): void
    {
    }

    /**
     * @inheritdoc
     */
    public function clear(string $key): void
    {
    }
}

<?php

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Cache\Model;

/**
 * Data object handled by cache engines.
 */
class Data
{
    /**
     * @param string $data
     * @param int $ttl
     */
    public function __construct(
        public readonly string $data,
        public readonly int $ttl
    ) { }
}

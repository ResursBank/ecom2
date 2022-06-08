<?php

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Cache;

use Resursbank\Ecom\Exception\ValidationException;

/**
 * Basic methods utilised by all cache implementations.
 */
abstract class AbstractCache
{
    /**
     * To ensure our keys will function regardless of cache implementation we
     * limit what characters may be utilised as part of the key. The key cannot
     * be empty.
     *
     * @param string $key
     * @return void
     * @throws ValidationException
     */
    public function validateKey(string $key): void
    {
        if ($key === '') {
            throw new ValidationException('Cache key cannot be empty.');
        }

        if (preg_match('/[^a-zA-Z\d\-_]/', $key)) {
            throw new ValidationException(
                'Cache key contains illegal characters.'
            );
        }
    }
}

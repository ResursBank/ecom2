<?php

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Rco\Models;

/**
 * Defines a metadata item
 */
class MetaData
{
    public string $key;
    public string $value;

    public function __construct(string $key, string $value)
    {
        $this->key = $key;
        $this->value = $value;
    }
}

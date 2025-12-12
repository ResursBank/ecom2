<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\UserSettings;

class NullReader implements ReaderInterface
{
    public function read(Field $field): ?string
    {
        return null;
    }

    public function update(Field $field, mixed $value): void
    {
    }

    public function getUrl(Url $url): ?string
    {
        return null;
    }
}

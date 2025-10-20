<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\UserSettings;

interface ReaderInterface
{
    public function read(Field $field): ?string;
}

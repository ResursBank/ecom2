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

    /**
     * Update a user settings field value.
     */
    public function update(Field $field, mixed $value): void;

    /**
     * Use the integration to get a URL for the given Url enum.
     */
    public function getUrl(Url $url): ?string;
}

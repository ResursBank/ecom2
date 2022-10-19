<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\PaymentMethod\ApplicationFormSpecResponse;

use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Lib\Collection\Collection;

/**
 * @SuppressWarnings(PHPMD.LongClassName)
 */
class ApplicationFormSpecElementResponseCollection extends Collection
{
    /**
     * @param array $data
     * @throws IllegalTypeException
     */
    public function __construct(array $data)
    {
        parent::__construct(data: $data, type: ApplicationFormSpecElementResponse::class);
    }

    /**
     * Filters out specified fields from collection
     *
     * @param string $property
     * @param array $fields
     * @return self
     * @throws IllegalTypeException
     */
    public function filter(string $property, array $fields): self
    {
        $filtered = array_filter(
            array: $this->getData(),
            callback: static function ($element) use ($fields, $property) {
                return !in_array(
                    needle: $element->{$property},
                    haystack: $fields,
                    strict: true
                );
            }
        );
        return new self(data: $filtered);
    }
}

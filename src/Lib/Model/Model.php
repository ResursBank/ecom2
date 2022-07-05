<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model;

use Resursbank\Ecom\Lib\Collection\Collection;

/**
 * Defines the basic structure of an Ecom model
 */
class Model
{
    /**
     * Converts the object to an array suitable for use with the Curl library
     *
     * @param mixed $item
     * @return array
     */
    public function toArray(mixed $item = null): array
    {
        if (!$item) {
            $item = $this;
        }

        $data = [];
        foreach ((array)$item as $name => $value) {
            if (is_object($value) || is_array($value)) {
                if ($value instanceof Collection) {
                    $data[$name] = $this->toArray(item: $value->toArray());
                } else {
                    $data[$name] = $this->toArray(item: $value);
                }
            } else {
                $data[$name] = $value;
            }
        }

        return $data;
    }
}

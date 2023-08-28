<?php

/**
* Copyright © Resurs Bank AB. All rights reserved.
* See LICENSE for license details.
*/

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco;

use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Validation\StringValidation;

/**
 * Implementation of TrackingDto object.
 */
class Tracking extends Model
{
    public function __construct(
        public readonly ?string $url = null,
        private readonly StringValidation $stringValidation = new StringValidation()
    ) {
        $this->validateUrl();
    }

    private function validateUrl(): void
    {
        // Comparison to empty string added to get around API returning this value.
        if ($this->url === null || $this->url === '') {
            return;
        }

        $this->stringValidation->matchRegex(
            value: $this->url,
            pattern: '/^https?:\/\/[-a-zA-Z0-9+&@#\/%?=~_|!:,.;]*[-a-zA-Z0-9+&@#\/%=~_|]/'
        );
    }
}

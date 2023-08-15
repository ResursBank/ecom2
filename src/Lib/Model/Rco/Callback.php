<?php

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco;

use Resursbank\Ecom\Exception\Validation\IllegalCharsetException;
use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Validation\StringValidation;

/**
 * Implementation of CallbackDto object.
 */
class Callback extends Model
{
    /**
     * @throws IllegalCharsetException
     */
    public function __construct(
        public readonly string $url,
        private readonly StringValidation $stringValidation = new StringValidation()
    ) {
        $this->validateUrl();
    }

    /**
     * @throws IllegalCharsetException
     */
    private function validateUrl(): void
    {
        $this->stringValidation->matchRegex(
            value: $this->url,
            pattern: '/https?:\/\/[-a-zA-Z0-9+&@#\/%?=~_|!:,.;]*[-a-zA-Z0-9+&@#\/%=~_|]/'
        );
    }
}

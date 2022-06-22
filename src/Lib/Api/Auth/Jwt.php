<?php

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Api\Auth;

use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Lib\Validation\StringValidation;

/**
 * Defines JSON Token API authentication.
 */
class Jwt
{
    /**
     * @param string $id
     * @param string $secret
     * @param StringValidation $stringValidation
     * @throws EmptyValueException
     * @todo Add charset validation of id and secret.
     */
    public function __construct(
        public readonly string $id,
        public readonly string $secret,
        private readonly StringValidation $stringValidation = new StringValidation()
    ) {
        $this->stringValidation->notEmpty(value: $this->id);
        $this->stringValidation->notEmpty(value: $this->secret);
    }
}

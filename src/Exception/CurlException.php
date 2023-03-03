<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Exception;

use Exception;
use JsonException;
use Resursbank\Ecom\Lib\Network\Curl\ErrorTranslator;
use stdClass;
use Throwable;

/**
 * Exceptions thrown from CURL requests.
 */
class CurlException extends Exception
{
    /**
     * Assign properties.
     */
    public function __construct(
        string $message,
        int $code,
        public readonly string|bool $body,
        public readonly int $httpCode = 0,
        ?Throwable $previous = null
    ) {
        parent::__construct(
            message: $message,
            code: $code,
            previous: $previous
        );
    }

    /**
     * @throws ConfigException
     * @throws JsonException
     */
    public function getDetails(): array
    {
        $result = [];

        if ($this->httpCode !== 400 || empty($this->body)) {
            return $result;
        }

        $body = json_decode(
            json: $this->body,
            associative: false,
            depth: 256,
            flags: JSON_THROW_ON_ERROR
        );

        if (
            isset($body->parameters) &&
            $body->parameters instanceof stdClass
        ) {
            foreach ($body->parameters as $property => $message) {
                $result[] = ErrorTranslator::get(
                    errorMessage: $property . ' ' . $message
                );
            }
        }

        return $result;
    }
}

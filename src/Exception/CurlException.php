<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Exception;

use Exception;
use JsonException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Model\Network\Response\Error;
use Resursbank\Ecom\Lib\Network\Curl\ErrorTranslator;
use Resursbank\Ecom\Lib\Utilities\DataConverter;
use stdClass;
use Throwable;

use function is_string;

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
        if (
            $this->httpCode !== 400 ||
            empty($this->body) ||
            !is_string(value: $this->body)
        ) {
            return [];
        }

        $body = json_decode(
            json: $this->body,
            associative: false,
            depth: 256,
            flags: JSON_THROW_ON_ERROR
        );

        return $this->extractParameters(body: $body);
    }

    /**
     * Attempts to convert body property value to an instance of Error model.
     * This will be available in some cases, as such Exceptions are expected and
     * not treated as actual errors.
     */
    public function getError(): ?Error
    {
        $result = null;

        if (!is_string(value: $this->body) || $this->body === '') {
            return null;
        }

        try {
            $body = json_decode(
                json: $this->body,
                associative: false,
                depth: 256,
                flags: JSON_THROW_ON_ERROR
            );

            if (!$body instanceof stdClass) {
                throw new IllegalValueException(message: 'Not an object.');
            }

            $error = DataConverter::stdClassToType(
                object: $body,
                type: Error::class
            );

            if ($error instanceof Error) {
                $result = $error;
            }
        } catch (Throwable) {
            // Do nothing. Body is not necessarily an Error model.
        }

        return $result;
    }

    /**
     * Extract parameters from body.
     *
     * @throws ConfigException
     */
    private function extractParameters(mixed $body): array
    {
        $result = [];

        if (
            $body instanceof stdClass &&
            isset($body->parameters) &&
            $body->parameters instanceof stdClass
        ) {
            /* @phpstan-ignore-next-line */
            foreach ($body->parameters as $property => $message) {
                $result[] = $this->getProperProperty(
                    property: $property,
                    message: $message
                );
            }
        }

        return $result;
    }

    /**
     * Get a translation from properties for where we are missing translations with untranslated parameters.
     *
     * @throws ConfigException
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    private function getProperProperty(string $property, string $message): string
    {
        // Generate formatted message strings
        $formattedFullPropertyError = ErrorTranslator::reformatMessage(
            message: "$property $message"
        );
        $formattedSimplePropertyError = ErrorTranslator::reformatMessage(
            message: $property
        );

        // Translate the full property + message
        $fullPropertyError = ErrorTranslator::get(
            errorMessage: "$property $message"
        );

        // Translate the simple property
        $simplePropertyError = ErrorTranslator::get(errorMessage: $property);

        // Check if the full property error is a valid translation
        if ($fullPropertyError !== $formattedFullPropertyError) {
            return $fullPropertyError;
        }

        // Check if the simple property error is a valid translation
        if ($simplePropertyError !== $formattedSimplePropertyError) {
            return $simplePropertyError;
        }

        // Fallback to concatenated property and message if no valid translations are found
        return "$property $message";
    }
}

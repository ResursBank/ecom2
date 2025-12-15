<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Exception;

use Exception;
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

    public function getDetails(): string
    {
        if (!is_string(value: $this->body) || $this->body === '') {
            return $this->getMessage();
        }

        try {
            $data = json_decode(
                json: $this->body,
                associative: true,
                depth: 512,
                flags: JSON_THROW_ON_ERROR
            );

            if (
                isset($data['validationErrors']) &&
                is_array(value: $data['validationErrors'])
            ) {
                foreach ($data['validationErrors'] as $error) {
                    if (!is_array(value: $error)) {
                        continue;
                    }

                    $translated = $this->extractParameters(error: $error);

                    if ($translated !== '') {
                        return $translated;
                    }
                }
            }
        } catch (Throwable) {
            // Intentionally silent
        }

        return $this->getMessage();
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
     * Translate a single validation error using ErrorTranslator.
     *
     * @param array<string, mixed> $error
     * @throws ConfigException
     */
    private function extractParameters(array $error): string
    {
        $fieldName = trim(string: $error['fieldName'] ?? '');
        $message = trim(string: $error['message'] ?? '');

        if ($fieldName === '' || $message === '') {
            return '';
        }

        // This is where translation belongs. As before.
        return ErrorTranslator::get(errorMessage: $fieldName . ' ' . $message);
    }
}

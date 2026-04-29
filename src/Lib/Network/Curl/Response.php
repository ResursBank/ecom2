<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Network\Curl;

use CurlHandle;
use JsonException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Exception\Validation\NotJsonEncodedException;
use Resursbank\Ecom\Lib\Utilities\Strings;
use stdClass;

use function is_int;

/**
 * Collection of methods to handle CURL request response data.
 */
class Response
{
    /**
     * Resolve body content from request response as object, decoded from JSON.
     *
     * @throws IllegalValueException
     * @throws NotJsonEncodedException
     * @throws EmptyValueException
     * @throws JsonException
     */
    public static function getJsonBody(string $body): stdClass
    {
        if (!Strings::notEmpty(value: $body)) {
            throw new EmptyValueException(message: 'Body cannot be empty.');
        }

        if (!Strings::isJson(value: $body)) {
            throw new NotJsonEncodedException(
                message: 'Body is not valid JSON.'
            );
        }

        $content = json_decode(
            json: $body,
            associative: false,
            depth: 512,
            flags: JSON_THROW_ON_ERROR
        );

        // Payment Method Elements API will return anonymous arrays.
        if (is_array(value: $content)) {
            return (object) ['data' => $content];
        }

        if (!$content instanceof stdClass) {
            throw new IllegalValueException(
                message: 'Decoded JSON body is not an object.'
            );
        }

        return $content;
    }

    /**
     * Type-safe wrapper to extract response code from request.
     *
     * @throws IllegalTypeException
     */
    public static function getCode(CurlHandle $ch): int
    {
        $code = curl_getinfo(handle: $ch, option: CURLINFO_RESPONSE_CODE);

        if (is_numeric(value: $code)) {
            $code = (int) $code;
        }

        if (!is_int(value: $code)) {
            throw new IllegalTypeException(
                message: 'Curl response code is not an integer.'
            );
        }

        return $code;
    }
}

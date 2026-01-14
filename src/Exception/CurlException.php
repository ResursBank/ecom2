<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Exception;

use Exception;
use JsonException;
use Resursbank\Ecom\Exception\Enum\InvalidFieldName;
use Throwable;
use ValueError;

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
     * Append validation error details from the API to the supplied message.
     *
     * During the payment creation process we may raise a CurlException as a
     * result of the input data to the API being rejected, for example if the
     * phone number or email supplied by the customer passes inspections made by
     * the platform, but is rejected by the Resurs Bank API due to format or
     * validation rules.
     *
     * This method attempts to extract those validation error details from
     * the response body and append them to the exception message for better
     * clarity.
     *
     * If this fails, the $msg is returned unmodified. $msg is intended to be
     * something like: "Failed to create payment at Resurs Bank.". We will then
     * append the details to that.
     *
     * This is technically not exclusive to the payment creation process, but
     * currently that's the only place we expect to see these kinds of errors.
     *
     * @throws ConfigException
     */
    public function getDetailedMessage(string $msg): string
    {
        try {
            $invalidField = $this->getInvalidFieldName();

            // Map field variants to their corresponding error phrase IDs.
            $phraseId = match ($invalidField) {
                InvalidFieldName::GOVERNMENT_ID => 'invalid-government-id',
                InvalidFieldName::PHONE => 'invalid-phone-number',
                InvalidFieldName::EMAIL => 'invalid-email-address',
                default => null,
            };

            if ($phraseId !== null) {
                return $msg . ' ' . Translator::translate(phraseId: $phraseId);
            }
        } catch (Throwable $error) {
            Config::getLogger()->error(message: $error);
        }

        return $msg;
    }

    /**
     * Extract the invalid field name from the response body validation errors.
     *
     * @throws JsonException
     */
    public function getInvalidFieldName(): InvalidFieldName
    {
        $body = json_decode(
            json: (string) $this->body,
            associative: true,
            depth: 256,
            flags: JSON_THROW_ON_ERROR
        );

        $fieldName = (
            is_array(value: $body) &&
            isset($body['validationErrors'][0]['fieldName'])
        ) ? (string) $body['validationErrors'][0]['fieldName'] : '';

        if ($fieldName === '') {
            return InvalidFieldName::UNKNOWN;
        }

        try {
            return InvalidFieldName::from(value: $fieldName);
        } catch (ValueError) {
            return InvalidFieldName::UNKNOWN;
        }
    }
}

<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Customer;

use JsonException;
use ReflectionException;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\ApiException;
use Resursbank\Ecom\Exception\AuthException;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\CurlException;
use Resursbank\Ecom\Exception\GetAddressException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Log\Traits\ExceptionLog;
use Resursbank\Ecom\Lib\Model\Address;
use Resursbank\Ecom\Lib\Model\CustomerType;
use Resursbank\Ecom\Lib\Model\Widget\GetAddress\GetAddressRequest;
use Resursbank\Ecom\Lib\Utilities\DataConverter;
use Resursbank\Ecom\Module\Customer\Api\GetAddress;
use stdClass;
use Throwable;

/**
 * Customer repository.
 */
class Repository
{
    use ExceptionLog;

    /**
     * Session key (without prefix) for government id.
     */
    public const SESSION_KEY_SSN_DATA = 'ssn_data';

    /**
     * @throws AuthException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws GetAddressException
     * @throws IllegalTypeException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ValidationException
     * @throws ApiException
     * @throws ConfigException
     */
    public static function getAddress(
        string $governmentId,
        CustomerType $customerType,
        GetAddress $api = new GetAddress()
    ): Address {
        try {
            return $api->call(
                governmentId: $governmentId,
                customerType: $customerType
            );
        } catch (Throwable $e) {
            self::logException(exception: $e);

            throw $e;
        }
    }

    /**
     * Store SSN data in session.
     *
     * NOTE: $sessionHandler to support testing with mocked session handler.
     *
     * @throws ConfigException
     */
    public static function setSsnData(
        GetAddressRequest $data
    ): void {
        try {
            Config::getSessionHandler()->set(
                key: self::SESSION_KEY_SSN_DATA,
                val: json_encode(value: $data, flags: JSON_THROW_ON_ERROR)
            );
        } catch (Throwable $e) {
            Config::getLogger()->info(
                message: 'Unable to set SSN data in session'
            );
            Config::getLogger()->debug(message: $e->getMessage());
            // Failing is harmless, client can supply info on gateway.
        }
    }

    /**
     * Clear SSN data from PHP session.
     *
     * During the authorization process in an integration we may expect the SSN
     * data to be supplied at some point, and this may fail, in which case we
     * must clear previously stored data to avoid submitting inaccurate
     * information to the gateway.
     *
     * @throws ConfigException
     */
    public static function clearSsnData(): void
    {
        Config::getSessionHandler()->delete(key: self::SESSION_KEY_SSN_DATA);
    }

    /**
     * Get SSN data from PHP session.
     *
     * NOTE: $sessionHandler to support testing with mocked session handler.
     *
     * @throws ConfigException
     */
    public static function getSsnData(): ?GetAddressRequest
    {
        $result = null;

        try {
            $data = Config::getSessionHandler()->get(
                key: self::SESSION_KEY_SSN_DATA
            );

            if ($data !== null && $data !== '') {
                $data = json_decode(
                    json: $data,
                    associative: false,
                    depth: 512,
                    flags: JSON_THROW_ON_ERROR
                );
            }

            if ($data instanceof stdClass) {
                $result = self::convertResponse(data: $data);
            }
        } catch (Throwable) {
            // Failing is harmless, client can supply info on gateway.
            Config::getLogger()->debug(message:
                "No SSN data available in session. Client likely did not " .
                "fetch address data from gateway. Client will need " .
                "to supply SSN data on gateway instead.");
        }

        return $result;
    }

    /**
     * Convert response to GetAddressRequest object.
     *
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws ReflectionException
     */
    private static function convertResponse(stdClass $data): GetAddressRequest
    {
        $result = DataConverter::stdClassToType(
            object: $data,
            type: GetAddressRequest::class
        );

        if (!$result instanceof GetAddressRequest) {
            throw new IllegalValueException(
                message: 'Session data is not SSN data.'
            );
        }

        return $result;
    }
}

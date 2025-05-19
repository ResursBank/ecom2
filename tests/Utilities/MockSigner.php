<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

/** @noinspection PhpMultipleClassDeclarationsInspection */

declare(strict_types=1);

namespace Resursbank\EcomTest\Utilities;

use JsonException;
use ReflectionException;
use Resursbank\Ecom\Exception\ApiException;
use Resursbank\Ecom\Exception\AttributeCombinationException;
use Resursbank\Ecom\Exception\AuthException;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\CurlException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Exception\Validation\MissingValueException;
use Resursbank\Ecom\Exception\Validation\NotJsonEncodedException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Model\Payment;
use Resursbank\Ecom\Lib\Network\AuthType;
use Resursbank\Ecom\Lib\Network\ContentType;
use Resursbank\Ecom\Lib\Network\Curl;
use Resursbank\Ecom\Lib\Network\RequestMethod;

/**
 * Handles mock signing in integration of MAPI payments.
 */
class MockSigner
{
    /**
     * Call customerUrl to trigger tasks.
     *
     * @throws ApiException
     * @throws AttributeCombinationException
     * @throws AuthException
     * @throws ConfigException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws NotJsonEncodedException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public static function callCustomerUrl(
        Payment $payment
    ): void {
        if ($payment->taskRedirectionUrls === null) {
            throw new MissingValueException(
                message: 'Missing task redirection urls, unable to retrieve ' .
                'customer URL.'
            );
        }

        $curl = new Curl(
            url: $payment->taskRedirectionUrls->customerUrl,
            requestMethod: RequestMethod::GET,
            authType: AuthType::NONE,
            responseContentType: ContentType::RAW
        );
        $curl->exec();
    }

    /**
     * Get Metadata entries as an array.
     *
     * @throws AttributeCombinationException
     * @throws JsonException
     * @throws ReflectionException
     */
    public static function getMetaDataArray(): array
    {
        $keys = [
            'MOCK_SIGNING',
            //'MOCK_CO_SIGNING' .
            'MOCK_AUTH',
            //'MOCK_CO_AUTH',
            'MOCK_CONSENT',
            'MOCK_FORM',
            'MOCK_PRE_SIGN',
            'MOCK_IDENTITY_CHECK',
            'MOCK_CUSTOMER_LANDING',
            //'MOCK_EXTERNAL_INVOICE_REFERENCE'
        ];

        $data = [];

        foreach ($keys as $key) {
            $data[] = new Payment\Metadata\Entry(key: $key, value: 'SUCCESS');
        }

        return $data;
    }

    /**
     * Fetches Metadata object for automatic auth/sign.
     *
     * @throws AttributeCombinationException
     * @throws IllegalTypeException
     * @throws JsonException
     * @throws ReflectionException
     */
    public static function getMetadata(): Payment\Metadata
    {
        return new Payment\Metadata(
            custom: new Payment\Metadata\EntryCollection(
                data: self::getMetaDataArray()
            )
        );
    }
}

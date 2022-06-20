<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Rco\Api;

use Resursbank\Ecom\Lib\Curl;
use Resursbank\Ecom\Lib\Utilities\DataConverter;
use Resursbank\Ecom\Module\Rco\Models\InitPayment\Request;
use Resursbank\Ecom\Module\Rco\Models\InitPayment\Response;

/**
 * Handles creation of RCO payment sessions
 */
class InitPayment
{
    /**
     * Makes call to the API
     *
     * @param Request $request
     * @param string $orderReference
     * @return Response
     * @throws \ReflectionException
     */
    public static function call(Request $request, string $orderReference): Response
    {
        $curl = new Curl\Curl();
        $response = $curl->post(
            url: self::getApiUrl(orderReference: $orderReference),
            data: $request->toArray()
        );

        return DataConverter::stdClassToType(
            object: $response,
            type: Response::class
        );
    }

    /**
     * Gets the API URL to use
     *
     * @param string $orderReference
     * @return string
     */
    private static function getApiUrl(string $orderReference): string
    {
        return self::getApiHostname() . '/payments/' . $orderReference;
    }

    /**
     * Gets API hostname
     *
     * @return string
     */
    private static function getApiHostname(): string
    {
        return '';
    }
}
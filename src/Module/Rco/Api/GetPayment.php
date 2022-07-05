<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Rco\Api;

use JsonException;
use ReflectionException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Network\AuthType;
use Resursbank\Ecom\Lib\Network\ContentType;
use Resursbank\Ecom\Lib\Network\RequestMethod;
use Resursbank\Ecom\Lib\Utilities\DataConverter;
use Resursbank\Ecom\Module\Rco\Repository;
use stdClass;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\CurlException;
use Resursbank\Ecom\Lib\Network\Curl;
use Resursbank\Ecom\Module\Rco\Models\GetPayment\Response;

/**
 * Handles fetching of RCO payment sessions
 */
class GetPayment
{
    /**
     * Make call to API
     *
     * @param string $orderReference
     * @return Response
     * @throws ReflectionException
     * @throws JsonException
     * @throws ValidationException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     */
    public function call(string $orderReference): Response
    {
        $response = new stdClass();
        try {
            $curl = new Curl(
                url: $this->getApiUrl(orderReference: $orderReference),
                requestMethod: RequestMethod::GET,
                contentType: ContentType::URL,
                authType: AuthType::BASIC,
                responseContentType: ContentType::JSON
            );
            $response = $curl->exec();
        } catch (CurlException $exception) {
            Config::$instance->logger->error(message: $exception);
        }

        return DataConverter::stdClassToType(
            object: $response->body,
            type: Response::class
        );
    }

    /**
     * Gets the API URL to use
     *
     * @param string $orderReference
     * @return string
     */
    private function getApiUrl(string $orderReference): string
    {
        return 'https://' . Repository::getApiHostname() . '/checkout/payments/' . $orderReference;
    }
}

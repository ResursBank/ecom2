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
use Resursbank\Ecom\Module\Rco\Repository;
use stdClass;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\CurlException;
use Resursbank\Ecom\Lib\Network\Curl;
use Resursbank\Ecom\Lib\Utilities\DataConverter;
use Resursbank\Ecom\Module\Rco\Models\UpdatePayment\Request;
use Resursbank\Ecom\Module\Rco\Models\UpdatePayment\Response;

/**
 * Handles updates of RCO payment sessions
 *
 * @SuppressWarnings (PHPMD.CouplingBetweenObjects)
 */
class UpdatePayment
{
    /**
     * Makes call to the API
     *
     * @param Request $request
     * @param string $orderReference
     * @return Response
     * @throws JsonException
     * @throws ReflectionException
     * @throws ValidationException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     */
    public function call(Request $request, string $orderReference): Response
    {
        $response = new stdClass();
        try {
            $response = Curl::put(
                url: $this->getApiUrl(orderReference: $orderReference),
                payload: $request->toArray(),
                authType: AuthType::BASIC,
                responseContentType: ContentType::RAW
            );
        } catch (CurlException $exception) {
            Config::$instance->logger->error(message: $exception);
        }

        $responseObj = new stdClass();
        $responseObj->message = $response->body->message;
        $responseObj->code = $response->code;

        return DataConverter::stdClassToType(
            object: $responseObj,
            type: Response::class
        );
    }

    /**
     * @param string $orderReference
     * @return string
     */
    private function getApiUrl(string $orderReference): string
    {
        return 'https://' . Repository::getApiHostname() . '/checkout/payments/' . $orderReference;
    }
}

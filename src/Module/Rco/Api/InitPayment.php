<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Rco\Api;

use ReflectionException;
use Resursbank\Ecom\Module\Rco\Repository;
use stdClass;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\CurlException;
use Resursbank\Ecom\Lib\Network\Curl;
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
     * @throws ReflectionException
     */
    public function call(Request $request, string $orderReference): Response
    {
        $curl = new Curl();
        $response = new stdClass();
        try {
            $response = $curl->post(
                url: $this->getApiUrl(orderReference: $orderReference),
                data: $request->toArray()
            );
        } catch (CurlException $exception) {
            Config::$instance->logger->error(message: $exception);
        }

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
    private function getApiUrl(string $orderReference): string
    {
        return $this->getApiHostname() . '/checkout/payments/' . $orderReference;
    }

    /**
     * Gets API hostname
     *
     * @return string
     */
    private function getApiHostname(): string
    {
        if (Config::$instance->isProduction) {
            return Repository::HOSTNAME_PROD;
        }

        return Repository::HOSTNAME_TEST;
    }
}

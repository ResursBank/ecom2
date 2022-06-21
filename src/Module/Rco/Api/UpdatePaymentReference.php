<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Rco\Api;

use ReflectionException;
use stdClass;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\CurlException;
use Resursbank\Ecom\Lib\Network\Curl;
use Resursbank\Ecom\Lib\Utilities\DataConverter;
use Resursbank\Ecom\Module\Rco\Models\UpdatePaymentReference\Request;
use Resursbank\Ecom\Module\Rco\Models\UpdatePaymentReference\Response;
use Resursbank\Ecom\Module\Rco\Repository;

/**
 * Handles updates of the RCO payment reference
 */
class UpdatePaymentReference
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
            $response = $curl->put(
                url:  $this->getApiUrl(orderReference: $orderReference),
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

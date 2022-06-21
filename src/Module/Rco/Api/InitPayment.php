<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Rco\Api;

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
    public function __construct(private readonly Config $config)
    {
    }

    /**
     * Makes call to the API
     *
     * @param Request $request
     * @param string $orderReference
     * @return Response
     * @throws \ReflectionException
     */
    public function call(Request $request, string $orderReference): Response
    {
        $curl = new Curl();
        try {
            $response = $curl->post(
                url: $this->getApiUrl(orderReference: $orderReference),
                data: $request->toArray()
            );
        } catch (CurlException $exception) {
            $this->config->logger->error(message: $exception);
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
        return self::getApiHostname() . '/payments/' . $orderReference;
    }

    /**
     * Gets API hostname
     *
     * @return string
     */
    private function getApiHostname(): string
    {
        // @todo Check if we're in production or test and return appropriate hostname
        return '';
    }
}
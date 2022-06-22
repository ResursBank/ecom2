<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Rco\Api;

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
    public function call(string $orderReference): Response
    {
        $curl = new Curl();
        $response = new stdClass();
        try {
            $response = $curl->get(url: $this->getApiUrl(orderReference: $orderReference));
        } catch (CurlException $exception) {
            Config::$instance->logger->error(message: $exception);
        }

        return $response;
    }

    /**
     * Gets the API URL to use
     *
     * @param string $orderReference
     * @return string
     */
    private function getApiUrl(string $orderReference): string
    {
        return Repository::getApiHostname() . '/checkout/payments/' . $orderReference;
    }
}

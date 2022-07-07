<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\RcoCallback\Api;

use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\CurlException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Lib\Network\AuthType;
use Resursbank\Ecom\Lib\Network\ContentType;
use Resursbank\Ecom\Lib\Network\Curl;
use Resursbank\Ecom\Lib\Network\RequestMethod;
use Resursbank\Ecom\Lib\Utilities\DataConverter;
use Resursbank\Ecom\Module\RcoCallback\Models\CallbackCollection;
use Resursbank\Ecom\Module\RcoCallback\Repository;

class GetCallbacks
{
    public function call(): CallbackCollection
    {
        if (!isset(Config::$instance->basicAuth)) {
            throw new EmptyValueException(message: 'Basic auth credentials not set in Config');
        }

        $curl = new Curl(
            url: $this->getApiUrl(),
            requestMethod: RequestMethod::GET,
            contentType: ContentType::EMPTY,
            authType: AuthType::BASIC,
            responseContentType: ContentType::JSON
        );

        try {
            $response = $curl->exec();
            return DataConverter::stdClassToType(
                object: $response->body,
                type: CallbackCollection::class
            );
        } catch (CurlException $exception) {
            Config::$instance->logger->error(message: $exception);
            throw $exception;
        }
    }

    /**
     * Gets the API URL to use
     *
     * @param string $eventName
     * @return string
     */
    private function getApiUrl(): string
    {
        return 'https://' . Repository::getApiHostname() . '/callbacks';
    }
}

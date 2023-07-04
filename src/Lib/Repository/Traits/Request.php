<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Repository\Traits;

use JsonException;
use ReflectionException;
use Resursbank\Ecom\Exception\ApiException;
use Resursbank\Ecom\Exception\AuthException;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\CurlException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Api\Mapi;
use Resursbank\Ecom\Lib\Api\Rco;
use Resursbank\Ecom\Lib\Collection\Collection;
use Resursbank\Ecom\Lib\Log\Traits\ExceptionLog;
use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Network\AuthType;
use Resursbank\Ecom\Lib\Network\ContentType;
use Resursbank\Ecom\Lib\Network\Curl;
use Resursbank\Ecom\Lib\Network\RequestMethod;

/**
 * HTTP Requests centralized for both RCO+ and MAPI related calls.
 */
class Request
{
    use ExceptionLog;
    use ModelConverter;
    use DataResolver;

    /**
     * @param class-string $model | Convert cached data to model instance(s).
     * @param array $params
     * @throws IllegalTypeException
     */
    public function __construct(
        protected readonly string $model,
        protected readonly string $route,
        protected readonly RequestMethod $requestMethod,
        protected Mapi|Rco $api,
        protected readonly array $params = [],
        protected readonly string $extractProperty = '',
        protected readonly array $headers = []
    ) {
        $this->validateModel(model: $model);
    }

    /**
     * @throws ApiException
     * @throws AuthException
     * @throws ConfigException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws ValidationException
     * @throws JsonException
     * @throws ReflectionException
     * @throws IllegalValueException
     */
    public function call(): Collection|Model
    {
        $curl = new Curl(
            url: $this->api->getUrl(
                route: $this->route
            ),
            requestMethod: $this->requestMethod,
            payload: $this->params,
            contentType: ContentType::URL,
            authType: AuthType::JWT,
            responseContentType: ContentType::JSON,
            headers: $this->headers
        );

        $data = $curl->exec()->body;

        return $this->convertToModel(
            data: $this->resolveResponseData(
                data: $data,
                extractProperty: $this->extractProperty
            ),
            model: $this->model
        );
    }
}

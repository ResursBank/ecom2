<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Repository\Traits;

use Closure;
use InvalidArgumentException;
use JsonException;
use ReflectionException;
use ReflectionFunction;
use Resursbank\Ecom\Exception\ApiException;
use Resursbank\Ecom\Exception\AttributeCombinationException;
use Resursbank\Ecom\Exception\AuthException;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\CurlException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Exception\Validation\NotJsonEncodedException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Api\Mapi;
use Resursbank\Ecom\Lib\Api\Rws;
use Resursbank\Ecom\Lib\Collection\Collection;
use Resursbank\Ecom\Lib\Log\Traits\ExceptionLog;
use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Network\AuthType;
use Resursbank\Ecom\Lib\Network\ContentType;
use Resursbank\Ecom\Lib\Network\Curl;
use Resursbank\Ecom\Lib\Network\RequestMethod;

/**
 * HTTP Requests centralized for MAPI related calls.
 */
class Request
{
    use ExceptionLog;
    use ModelConverter;
    use DataResolver;

    /**
     * @param class-string $model | Convert cached data to model instance(s).
     * @throws IllegalTypeException
     * @throws ReflectionException
     */
    public function __construct(
        protected readonly string $model,
        protected readonly string $route,
        protected readonly RequestMethod $requestMethod,
        protected Mapi|Rws $api,
        protected readonly array $params = [],
        protected readonly string $extractProperty = '',
        protected readonly array $headers = [],
        protected readonly ContentType $contentType = ContentType::JSON,
        protected readonly ?Closure $customModelConverter = null
    ) {
        // Validate the closure signature if provided.
        if ($customModelConverter !== null) {
            $this->validateCustomModelConverter(
                callable: $customModelConverter
            );
        }

        $this->validateModel(model: $model);
    }

    /**
     * @throws ApiException
     * @throws AuthException
     * @throws ConfigException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ValidationException
     * @throws AttributeCombinationException
     * @throws NotJsonEncodedException
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function call(bool $forceObject = false): Collection|Model
    {
        $curl = new Curl(
            url: $this->api->getUrl(
                route: $this->route
            ),
            requestMethod: $this->requestMethod,
            headers: $this->headers,
            payload: $this->params,
            contentType: $this->contentType,
            authType: AuthType::JWT,
            responseContentType: ContentType::JSON,
            forceObject: $forceObject
        );

        $data = $this->resolveResponseData(
            data: $curl->exec()->body,
            extractProperty: $this->extractProperty
        );

        // Use custom model converter if provided.
        if ($this->customModelConverter !== null) {
            return ($this->customModelConverter)($data);
        }

        // Use generic model converter.
        return $this->convertToModel(data: $data, model: $this->model);
    }

    /**
     * Uses reflection API to validate the custom model converter has the
     * correct return type.
     *
     * @throws ReflectionException
     */
    private function validateCustomModelConverter(Closure $callable): void
    {
        $reflection = new ReflectionFunction(function: $callable);

        // Get return type.
        $returnType = explode(
            separator: '|',
            string: (string) $reflection->getReturnType()
        );

        // Must contain exactly two classes.
        if (count($returnType) !== 2) {
            throw new InvalidArgumentException(
                message: 'customModelConverter must return Collection or Model'
            );
        }

        // Must contain FQN of either Collection or Model.
        if (
            !in_array(
                needle: Collection::class,
                haystack: $returnType,
                strict: true
            )
        ) {
            throw new InvalidArgumentException(
                message: 'customModelConverter must be able to return Collection.'
            );
        }

        // Remove Collection from return type.
        if (
            !in_array(needle: Model::class, haystack: $returnType, strict: true)
        ) {
            throw new InvalidArgumentException(
                message: 'customModelConverter must be able to return Model.'
            );
        }
    }
}

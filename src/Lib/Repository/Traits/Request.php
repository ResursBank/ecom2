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
use Resursbank\Ecom\Lib\Api\PaymentMethodElements;
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
        protected Mapi|PaymentMethodElements $api,
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
    public function call(): Collection|Model
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
            responseContentType: ContentType::JSON
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
     * Confirm custom model converter return type and arguments.
     *
     * @throws ReflectionException
     */
    public function validateCustomModelConverter(Closure $callable): void
    {
        $reflection = new ReflectionFunction(function: $callable);

        $this->validateReturnType(reflection: $reflection);
        $this->validateArguments(reflection: $reflection);
    }

    /**
     * Validates the return type of the custom model converter.
     *
     * @throws InvalidArgumentException
     */
    private function validateReturnType(ReflectionFunction $reflection): void
    {
        // Get return type.
        $returnType = $reflection->getReturnType();

        if ($returnType === null) {
            throw new InvalidArgumentException(
                message: 'customModelConverter signature must allow for exactly two return types: Collection and Model.'
            );
        }

        $returnTypeString = (string) $returnType;
        $returnTypes = explode(separator: '|', string: $returnTypeString);

        // Must contain exactly two classes.
        if (count($returnTypes) !== 2) {
            throw new InvalidArgumentException(
                message: 'customModelConverter signature must allow for exactly two return types: Collection and Model.'
            );
        }

        // Must be able to return Collection.
        if (
            !in_array(
                needle: Collection::class,
                haystack: $returnTypes,
                strict: true
            )
        ) {
            throw new InvalidArgumentException(
                message: 'customModelConverter must be able to return Collection.'
            );
        }

        // Must be able to return Model.
        if (
            !in_array(
                needle: Model::class,
                haystack: $returnTypes,
                strict: true
            )
        ) {
            throw new InvalidArgumentException(
                message: 'customModelConverter must be able to return Model.'
            );
        }
    }

    /**
     * Validates the arguments of the custom model converter.
     *
     * @throws InvalidArgumentException
     */
    private function validateArguments(ReflectionFunction $reflection): void
    {
        $parameters = $reflection->getParameters();

        // Confirm exactly one argument is expected by the function.
        if (count($parameters) !== 1) {
            throw new InvalidArgumentException(
                message: 'customModelConverter must accept exactly one argument.'
            );
        }

        // Confirm that the argument is named data and is of the type stdClass.
        $parameter = $parameters[0];

        if (
            $parameter->getName() !== 'data' ||
            !$parameter->getType() ||
            /* @phpstan-ignore-next-line */
            $parameter->getType()->getName() !== 'stdClass'
        ) {
            throw new InvalidArgumentException(
                message: 'customModelConverter must accept a single argument named "data" of type stdClass.'
            );
        }
    }
}

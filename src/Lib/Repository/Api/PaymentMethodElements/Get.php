<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

/** @noinspection PhpMultipleClassDeclarationsInspection */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Repository\Api\PaymentMethodElements;

use Closure;
use ReflectionException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Lib\Api\PaymentMethodElements;
use Resursbank\Ecom\Lib\Log\Traits\ExceptionLog;
use Resursbank\Ecom\Lib\Network\RequestMethod;
use Resursbank\Ecom\Lib\Repository\Traits\DataResolver;
use Resursbank\Ecom\Lib\Repository\Traits\ModelConverter;
use Resursbank\Ecom\Lib\Repository\Traits\Request;

/**
 * Generic functionality to perform a GET call against PME API and convert the
 * response to model instance(s).
 */
class Get extends Request
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
        string $model,
        string $route,
        array $params = [],
        string $extractProperty = '',
        ?Closure $customModelConverter = null
    ) {
        parent::__construct(
            model: $model,
            route: $route,
            requestMethod: RequestMethod::GET,
            api: new PaymentMethodElements(),
            params: $params,
            extractProperty: $extractProperty,
            customModelConverter: $customModelConverter
        );
    }
}

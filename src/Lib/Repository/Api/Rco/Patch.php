<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

/** @noinspection PhpMultipleClassDeclarationsInspection */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Repository\Api\Rco;

use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Lib\Api\Rco;
use Resursbank\Ecom\Lib\Log\Traits\ExceptionLog;
use Resursbank\Ecom\Lib\Network\RequestMethod;
use Resursbank\Ecom\Lib\Repository\Traits\DataResolver;
use Resursbank\Ecom\Lib\Repository\Traits\ModelConverter;
use Resursbank\Ecom\Lib\Repository\Traits\Request;

/**
 * Generic functionality to perform a PATCH call against RCO+ and
 * convert the response to model instance(s).
 */
class Patch extends Request
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
        string $model,
        string $route,
        array $params = [],
        string $extractProperty = '',
        array $headers = []
    ) {
        parent::__construct(
            model: $model,
            route: $route,
            requestMethod: RequestMethod::PATCH,
            api: new Rco(),
            params: $params,
            extractProperty: $extractProperty,
            headers: $headers
        );
    }
}

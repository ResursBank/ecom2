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
 * Generic functionality to perform a GET call against RCO+ and
 * convert the response to model instance(s).
 */
class Get extends Request
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
        protected readonly array $params = [],
        protected readonly string $extractProperty = '',
        protected readonly array $headers = []
    ) {
        parent::__construct(
            model: $this->model,
            route: $this->route,
            requestMethod: RequestMethod::GET,
            api: new Rco(),
            params: $this->params,
            extractProperty: $this->extractProperty,
            headers: $this->headers
        );
    }
}

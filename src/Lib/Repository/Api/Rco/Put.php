<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

/** @noinspection PhpMultipleClassDeclarationsInspection */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Repository\Api\Rco;

use Resursbank\Ecom\Lib\Network\RequestMethod;

/**
 * Generic functionality to perform a PUT call against RCO+ and
 * convert the response to model instance(s).
 */
class Put extends Request
{
    public function __construct(
        string $route,
        string $version,
        array $params = []
    ) {
        parent::__construct(
            route: $route,
            version: $version,
            requestMethod: RequestMethod::PUT,
            params: $params
        );
    }
}

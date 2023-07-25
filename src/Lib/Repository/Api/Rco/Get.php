<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

/** @noinspection PhpMultipleClassDeclarationsInspection */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Repository\Api\Rco;

use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Lib\Network\RequestMethod;

/**
 * Generic functionality to perform a GET call against RCO+ and
 * convert the response to model instance(s).
 */
class Get extends Request
{
    /**
     * @throws IllegalTypeException
     * @throws EmptyValueException
     */
    public function __construct(
        string $route
    ) {
        parent::__construct(route: $route, requestMethod: RequestMethod::GET);
    }
}

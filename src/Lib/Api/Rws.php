<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Api;

use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Lib\Validation\StringValidation;

/**
 * API for Resurs Widget Service (RWS).
 */
class Rws
{
    public const URL_TEST = 'https://api.i.eks.aws.cld.resurs.com/api/resurs_widget_service/';
    public const PAYMENT_METHODS_ROUTE = 'payment-methods';

    public function __construct(
        private readonly StringValidation $stringValidation = new StringValidation()
    ) {
    }

    /**
     * @throws EmptyValueException
     */
    public function getUrl(
        string $route
    ): string {
        $this->stringValidation->notEmpty(value: $route);

        return self::URL_TEST . $route;
    }
}

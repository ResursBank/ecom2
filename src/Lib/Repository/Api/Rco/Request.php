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
use Resursbank\Ecom\Lib\Api\Rco;
use Resursbank\Ecom\Lib\Log\Traits\ExceptionLog;
use Resursbank\Ecom\Lib\Model\Network\Header;
use Resursbank\Ecom\Lib\Model\Rco\Checkout;
use Resursbank\Ecom\Lib\Network\ContentType;
use Resursbank\Ecom\Lib\Network\RequestMethod;
use Resursbank\Ecom\Lib\Repository\Traits\DataResolver;
use Resursbank\Ecom\Lib\Repository\Traits\ModelConverter;
use Resursbank\Ecom\Lib\Repository\Traits\Request as BaseRequest;

/**
 * Generic functionality to perform API calls against RCO+ and
 * convert the response to model instance(s).
 */
class Request extends BaseRequest
{
    use ExceptionLog;
    use ModelConverter;
    use DataResolver;

    /**
     * @throws IllegalTypeException
     * @throws EmptyValueException
     */
    public function __construct(
        string $route,
        RequestMethod $requestMethod,
        array $params = [],
        array $headers = [],
        string $version = ''
    ) {
        if ($version !== '') {
            $headers[] = new Header(key: 'X-Checkout-Version', value: $version);
        }

        parent::__construct(
            model: Checkout::class,
            route: $route,
            requestMethod: $requestMethod,
            api: new Rco(),
            params: $params,
            extractProperty: '',
            headers: $headers,
            contentType: ContentType::JSON
        );
    }
}

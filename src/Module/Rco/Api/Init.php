<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Rco\Api;

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
use Resursbank\Ecom\Lib\Api\Rco;
use Resursbank\Ecom\Lib\Model\Rco\Checkout;
use Resursbank\Ecom\Lib\Network\AuthType;
use Resursbank\Ecom\Lib\Network\ContentType;
use Resursbank\Ecom\Lib\Network\Curl;
use Resursbank\Ecom\Lib\Network\RequestMethod;
use Resursbank\Ecom\Lib\Utilities\DataConverter;
use stdClass;

/**
 * Initialize a new checkout session.
 */
class Init
{
    private Rco $rco;

    /**
     * Initialize Rco object.
     */
    public function __construct()
    {
        $this->rco = new Rco();
    }

    /**
     * @throws ValidationException
     * @throws CurlException
     * @throws IllegalValueException
     * @throws IllegalTypeException
     * @throws AuthException
     * @throws EmptyValueException
     * @throws JsonException
     * @throws ConfigException
     * @throws ReflectionException
     * @throws ApiException
     */
    public function call(Checkout $checkout): Checkout
    {
        $payload = [
            'orderReference' => $checkout->orderReference,
            'options' => $checkout->options,
            'locale' => $checkout->locale,
            'currency' => $checkout->currency,
            'cart' => $checkout->cart->toArray(),
            'customer' => $checkout->customer,
            'redirects' => $checkout->redirects,
            'callbacks' => $checkout->callbacks,
            'webhooks' => $checkout->webhooks,
            'checkboxes' => $checkout->checkboxes->toArray(),
            'merchant' => $checkout->merchant
        ];

        $curl = new Curl(
            url: $this->rco->getUrl(route: Rco::CHECKOUT_ROUTE),
            requestMethod: RequestMethod::POST,
            payload: $payload,
            contentType: ContentType::JSON,
            authType: AuthType::JWT,
            responseContentType: ContentType::JSON
        );

        $data = $curl->exec()->body;

        if (!$data instanceof stdClass) {
            throw new ApiException(
                message: 'Invalid response from API. Not an stdClass',
                code: 500
            );
        }

        $result = DataConverter::stdClassToType(
            object: $data,
            type: Checkout::class
        );

        if (!$result instanceof Checkout) {
            throw new IllegalValueException(
                message: 'Response is not an instance of ' . Checkout::class
            );
        }

        return $result;
    }
}

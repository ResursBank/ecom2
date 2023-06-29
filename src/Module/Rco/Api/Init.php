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
use Resursbank\Ecom\Lib\Model\Rco\Payment;
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
    public function call(Payment $payment): Payment
    {
        $payload = [
            'orderReference' => $payment->orderReference,
            'options' => $payment->options,
            'locale' => $payment->locale,
            'currency' => $payment->currency,
            'cart' => $payment->cart->toArray(),
            'customer' => $payment->customer,
            'redirects' => $payment->redirects,
            'callbacks' => $payment->callbacks,
            'webhooks' => $payment->webhooks,
            'checkboxes' => $payment->checkboxes->toArray(),
            'merchant' => $payment->merchant
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
            type: Payment::class
        );

        if (!$result instanceof Payment) {
            throw new IllegalValueException(
                message: 'Response is not an instance of ' . Payment::class
            );
        }

        return $result;
    }
}

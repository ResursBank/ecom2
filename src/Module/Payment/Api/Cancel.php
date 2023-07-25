<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Payment\Api;

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
use Resursbank\Ecom\Lib\Api\Mapi;
use Resursbank\Ecom\Lib\Model\Payment;
use Resursbank\Ecom\Lib\Model\Payment\Order\ActionLog\OrderLineCollection;
use Resursbank\Ecom\Lib\Network\AuthType;
use Resursbank\Ecom\Lib\Network\ContentType;
use Resursbank\Ecom\Lib\Network\Curl;
use Resursbank\Ecom\Lib\Network\RequestMethod;
use Resursbank\Ecom\Lib\Utilities\DataConverter;
use Throwable;

/**
 * POST /payments/{payment_id}/cancel
 */
class Cancel
{
    private Mapi $mapi;

    public function __construct()
    {
        $this->mapi = new Mapi();
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
     * @throws Throwable
     */
    public function call(
        string $paymentId,
        ?OrderLineCollection $orderLines = null,
        ?string $creator = null
    ): Payment {
        $payload = [];

        if ($orderLines) {
            $payload['orderLines'] = $orderLines->toArray();
        }

        if ($creator) {
            $payload['creator'] = $creator;
        }

        $curl = new Curl(
            url: $this->mapi->getUrl(
                route: Mapi::PAYMENT_ROUTE . '/' . $paymentId . '/cancel'
            ),
            requestMethod: RequestMethod::POST,
            payload: $payload,
            authType: AuthType::JWT,
            responseContentType: ContentType::JSON,
            forceObject: empty($payload)
        );

        $result = DataConverter::stdClassToType(
            object: $curl->exec()->body,
            type: Payment::class
        );

        if (!$result instanceof Payment) {
            throw new IllegalTypeException(message: 'Expected Payment');
        }

        return $result;
    }
}

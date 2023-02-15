<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Payment\Api\Order\ActionLog\OrderLines;

use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Lib\Api\Mapi;
use Resursbank\Ecom\Lib\Model\Payment;
use Resursbank\Ecom\Lib\Model\Payment\Order\ActionLog\OrderLineCollection;
use Resursbank\Ecom\Lib\Network\AuthType;
use Resursbank\Ecom\Lib\Network\ContentType;
use Resursbank\Ecom\Lib\Network\Curl;
use Resursbank\Ecom\Lib\Network\RequestMethod;
use Resursbank\Ecom\Lib\Utilities\DataConverter;
use stdClass;

class Add
{
    private Mapi $mapi;

    public function __construct()
    {
        $this->mapi = new Mapi();
    }

    public function call(
        string $paymentId,
        OrderLineCollection $orderLines
    ): Payment {
        $payload = [];
        $payload['orderLines'] = $orderLines->toArray();

        $curl = new Curl(
            url: $this->mapi->getUrl(
                route: Mapi::PAYMENT_ROUTE . '/' . $paymentId . '/order'
            ),
            requestMethod: RequestMethod::POST,
            payload: $payload,
            authType: AuthType::JWT,
            responseContentType: ContentType::JSON,
            forceObject: empty($payload)
        );

        $data = $curl->exec()->body;

        $content = $data instanceof stdClass ? $data : new stdClass();

        $result = DataConverter::stdClassToType(
            object: $content,
            type: Payment::class
        );

        if (!$result instanceof Payment) {
            throw new IllegalTypeException(message: 'Expected Payment');
        }

        return $result;
    }
}

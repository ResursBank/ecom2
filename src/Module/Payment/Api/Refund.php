<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Payment\Api;

use JsonException;
use Resursbank\Ecom\Exception\AuthException;
use Resursbank\Ecom\Exception\CurlException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Api\Mapi;
use Resursbank\Ecom\Lib\Network\AuthType;
use Resursbank\Ecom\Lib\Network\ContentType;
use Resursbank\Ecom\Lib\Network\Curl;
use Resursbank\Ecom\Lib\Network\RequestMethod;
use Resursbank\Ecom\Lib\Utilities\DataConverter;
use Resursbank\Ecom\Module\Payment\Models\Order\ActionLog\OrderLineCollection;
use Resursbank\Ecom\Module\Payment\Models\Payment;
use Symfony\Component\Config\Definition\Exception\InvalidTypeException;

/**
 * POST /payments/{payment_id}/refund
 */
class Refund
{
    /** @var Mapi  */
    private Mapi $mapi;

    public function __construct()
    {
        $this->mapi = new Mapi();
    }

    /**
     * @param string $paymentId
     * @param OrderLineCollection|null $orderLines
     * @param string|null $creator
     * @param string|null $transactionId
     * @return Payment
     * @throws JsonException
     * @throws \ReflectionException
     * @throws AuthException
     * @throws CurlException
     * @throws ValidationException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     */
    public function call(
        string $paymentId,
        ?OrderLineCollection $orderLines = null,
        ?string $creator = null,
        ?string $transactionId = null
    ): Payment {
        $payload = [];
        if ($orderLines) {
            $payload['orderLines'] = $orderLines->toArray();
        }
        if ($creator) {
            $payload['creator'] = $creator;
        }
        if ($transactionId) {
            $payload['transactionId'] = $transactionId;
        }
        
        $curl = new Curl(
            url: $this->mapi->getUrl(
                route: sprintf('%s/payments/%s/refund', Mapi::PAYMENT_ROUTE, $paymentId)
            ),
            requestMethod: RequestMethod::POST,
            payload: $payload,
            authType: AuthType::JWT,
            responseContentType: ContentType::JSON
        );

        $data = $curl->exec()->body;

        $content = ($data instanceof stdClass) ? $data : new stdClass();

        $result = DataConverter::stdClassToType(
            object: $content,
            type: Payment::class
        );

        if (!$result instanceof Payment) {
            throw new InvalidTypeException(message: "Expected Payment");
        }

        return $result;
    }
}

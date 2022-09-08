<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Payment\Api;

use Resursbank\Ecom\Lib\Api\Mapi;
use Resursbank\Ecom\Lib\Network\AuthType;
use Resursbank\Ecom\Lib\Network\ContentType;
use Resursbank\Ecom\Lib\Network\Curl;
use Resursbank\Ecom\Lib\Network\RequestMethod;
use Resursbank\Ecom\Lib\Utilities\DataConverter;
use Resursbank\Ecom\Module\Payment\Models\Order\ActionLog\OrderLineCollection;
use Resursbank\Ecom\Module\Payment\Models\Payment;
use stdClass;
use Symfony\Component\Config\Definition\Exception\InvalidTypeException;

/**
 * POST /payments/{payment_id}/cancel
 */
class Cancel
{
    /** @var Mapi  */
    private Mapi $mapi;

    public function __construct()
    {
        $this->mapi = new Mapi();
    }

    public function call(
        string $orderReference,
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
                route: sprintf('%s/payments/%s/cancel', Mapi::PAYMENT_ROUTE, $orderReference)
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
            throw new InvalidTypeException(message: "Exptected Payment");
        }

        return $result;
    }
}

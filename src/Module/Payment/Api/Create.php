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
use Resursbank\Ecom\Exception\CurlException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Api\Mapi;
use Resursbank\Ecom\Lib\Network\AuthType;
use Resursbank\Ecom\Lib\Network\ContentType;
use Resursbank\Ecom\Lib\Network\Curl;
use Resursbank\Ecom\Lib\Network\RequestMethod;
use Resursbank\Ecom\Lib\Utilities\DataConverter;
use Resursbank\Ecom\Module\Payment\Models\CreatePayment\Order;
use Resursbank\Ecom\Module\Payment\Models\Order\OrderLineCollection;
use Resursbank\Ecom\Module\Payment\Models\Payment;
use stdClass;

class Create
{
    /** @var Mapi  */
    private Mapi $mapi;

    public function __construct()
    {
        $this->mapi = new Mapi();
    }

    /**
     * @param string $storeId
     * @param string $paymentMethodId
     * @param OrderLineCollection $orderLines
     * @return Payment
     * @throws ApiException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws AuthException
     * @throws CurlException
     * @throws ValidationException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     */
    public function call(
        string $storeId,
        string $paymentMethodId,
        OrderLineCollection $orderLines,
        ?string $orderReference = null
    ): Payment {
        $params = [
            'storeId' => $storeId,
            'paymentMethodId' => $paymentMethodId,
            'order' => [
                'orderLines' => $orderLines->toArray()
            ]
        ];
        if ($orderReference) {
            $params['order']['orderReference'] = $orderReference;
        }

        $curl = new Curl(
            url: $this->mapi->getUrl(
                route: Mapi::PAYMENT_ROUTE . '/payments'
            ),
            requestMethod: RequestMethod::POST,
            payload: $params,
            contentType: ContentType::JSON,
            authType: AuthType::JWT,
            responseContentType: ContentType::JSON
        );

        $data = $curl->exec()->body;

        if (!$data instanceof stdClass) {
            throw new ApiException(
                message: 'Invalid response from API. Not an stdClass.',
                code: 500,
            );
        }

        $result = DataConverter::stdClassToType(
            $data,
            Payment::class
        );

        if (!$result instanceof Payment) {
            throw new IllegalValueException(
                'Response is not an instance of ' . Payment::class
            );
        }

        return $result;
    }
}

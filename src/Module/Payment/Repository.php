<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Payment;

use Exception;
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
use Resursbank\Ecom\Lib\Collection\Collection;
use Resursbank\Ecom\Module\Payment\Api\Cancel;
use Resursbank\Ecom\Module\Payment\Api\Capture;
use Resursbank\Ecom\Lib\Log\Traits\ExceptionLog;
use Resursbank\Ecom\Lib\Network\AuthType;
use Resursbank\Ecom\Lib\Network\ContentType;
use Resursbank\Ecom\Lib\Network\Curl;
use Resursbank\Ecom\Lib\Network\RequestMethod;
use Resursbank\Ecom\Lib\Utilities\DataConverter;
use Resursbank\Ecom\Module\Payment\Api\Refund;
use Resursbank\Ecom\Module\Payment\Api\Search;
use Resursbank\Ecom\Module\Payment\Api\GetPayment;
use Resursbank\Ecom\Module\Payment\Models\Order\ActionLog\OrderLineCollection;
use Resursbank\Ecom\Module\Payment\Models\Payment;
use stdClass;

/**
 * Payment repository.
 */
class Repository
{
    use ExceptionLog;

    /**
     * @param string $storeId
     * @param string $orderReference
     * @param string $governmentId
     * @param Search $api
     * @return Collection
     * @throws AuthException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public static function search(
        string $storeId,
        string $orderReference = '',
        string $governmentId = '',
        Search $api = new Search()
    ): Collection {
        return $api->call(
            storeId: $storeId,
            orderReference: $orderReference,
            governmentId: $governmentId
        );
    }

    /**
     * @param string $orderReference
     * @param GetPayment $api
     *
     * @return Payment
     * @throws AuthException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public static function getPayment(
        string $orderReference,
        GetPayment $api = new GetPayment()
    ): Payment {
        try {
            return $api->call(
                $orderReference
            );
        } catch (Exception $e) {
            self::logException(exception: $e);

            throw $e;
        }
    }

    /**
     * @throws IllegalTypeException
     * @throws ValidationException
     * @throws AuthException
     * @throws EmptyValueException
     * @throws CurlException
     * @throws JsonException
     * @throws ApiException
     * @throws ReflectionException
     */
    public static function createPayment(
        array $params
    ): Payment {
        $mapi = new Mapi();
        $curl = new Curl(
            url: $mapi->getUrl(
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
        
    /**
     * Capture payment
     *
     * @param string $paymentId
     * @param OrderLineCollection|null $orderLines
     * @param string|null $creator
     * @param string|null $transactionId
     * @param string|null $invoiceId
     * @return Payment
     * @throws AuthException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public static function capture(
        string $paymentId,
        ?OrderLineCollection $orderLines = null,
        ?string $creator = null,
        ?string $transactionId = null,
        ?string $invoiceId = null
    ): Payment {
        return (new Capture())->call(
            paymentId: $paymentId,
            orderLines: $orderLines,
            creator: $creator,
            transactionId: $transactionId,
            invoiceId: $invoiceId
        );
    }

    /**
     * Cancel payment
     *
     * @param string $paymentId
     * @param OrderLineCollection|null $orderLines
     * @param string|null $creator
     * @return Payment
     * @throws AuthException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public static function cancel(
        string $paymentId,
        ?OrderLineCollection $orderLines = null,
        ?string $creator = null
    ): Payment {
        return (new Cancel())->call(
            paymentId: $paymentId,
            orderLines: $orderLines,
            creator: $creator
        );
    }

    /**
     * Refund payment
     *
     * @param string $paymentId
     * @param OrderLineCollection|null $orderLines
     * @param string|null $creator
     * @param string|null $transactionId
     * @return Payment
     * @throws AuthException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public static function refund(
        string $paymentId,
        ?OrderLineCollection $orderLines = null,
        ?string $creator = null,
        ?string $transactionId = null
    ): Payment {
        return (new Refund())->call(
            paymentId: $paymentId,
            orderLines: $orderLines,
            creator: $creator,
            transactionId: $transactionId
        );
    }
}

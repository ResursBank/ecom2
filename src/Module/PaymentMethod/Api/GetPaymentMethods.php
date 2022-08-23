<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

/** @noinspection PhpMultipleClassDeclarationsInspection */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\PaymentMethod\Api;

use JsonException;
use ReflectionException;
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
use Resursbank\Ecom\Module\PaymentMethod\Models\PaymentMethod;
use Resursbank\Ecom\Module\PaymentMethod\Models\PaymentMethodCollection;
use stdClass;

use function is_array;

/**
 * API call to get PaymentMethods.
 */
class GetPaymentMethods
{
    /**
     * @param Mapi $mapi
     */
    public function __construct(
        private readonly Mapi $mapi = new Mapi()
    ) {
    }

    /**
     * Perform API request and assign response to this object.
     *
     * @param string $storeId
     * @param float|null $amount
     * @return PaymentMethodCollection
     * @throws AuthException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ValidationException
     * @todo Implement $sort and related tests after we have confirm of its structure.
     */
    public function call(
        string $storeId,
        ?float $amount = null,
    ): PaymentMethodCollection {
        $curl = new Curl(
            url: $this->mapi->getUrl(
                route: Mapi::COMMON_ROUTE . "/stores/$storeId/payment_methods"
            ),
            requestMethod: RequestMethod::GET,
            payload: compact(var_name: 'amount'),
            contentType: ContentType::URL,
            authType: AuthType::JWT,
            responseContentType: ContentType::JSON
        );

        $body = $curl->exec()->body;

        $content = (
            $body instanceof stdClass &&
            isset($body->paymentMethods) &&
            is_array(value: $body->paymentMethods)
        ) ? $body->paymentMethods : [];

        $result = DataConverter::arrayToCollection(
            data: $content,
            targetType: PaymentMethod::class
        );

        if (!$result instanceof PaymentMethodCollection) {
            throw new IllegalTypeException(
                message: 'Expected PaymentMethodCollection.'
            );
        }

        return $result;
    }
}

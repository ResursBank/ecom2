<?php

namespace Resursbank\Ecom\Module\Payment\Api;

use JsonException;
use Resursbank\Ecom\Exception\AuthException;
use Resursbank\Ecom\Exception\CurlException;
use Resursbank\Ecom\Exception\TypeException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Api\Mapi;
use Resursbank\Ecom\Lib\Network\AuthType;
use Resursbank\Ecom\Lib\Network\ContentType;
use Resursbank\Ecom\Lib\Network\Curl;
use Resursbank\Ecom\Lib\Network\RequestMethod;
use Resursbank\Ecom\Lib\Utilities\DataConverter;
use Resursbank\Ecom\Lib\Validation\StringValidation;
use Resursbank\Ecom\Module\Payment\Models\FindPaymentCollection;
use Resursbank\Ecom\Module\Payment\Models\Payment;
use Resursbank\Ecom\Module\Payment\Models\PaymentCollection;
use stdClass;

class FindPayment
{
    /**
     * @param Mapi $mapi
     * @param StringValidation $stringValidation
     */
    public function __construct(
        private readonly Mapi $mapi = new Mapi(),
        private readonly StringValidation $stringValidation = new StringValidation()
    ) {
    }

    /**
     * @param string $storeId
     * @param string $orderReference
     * @param string $governmentId
     * @return void
     * @throws JsonException
     * @throws AuthException
     * @throws CurlException
     * @throws TypeException
     * @throws ValidationException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws \ReflectionException
     */
    public function exec(string $storeId, string $orderReference, string $governmentId = '')
    {
        $payload = ['orderReference' => $orderReference];
        if (trim($governmentId) !== '') {
            $payload['governmentId'] = $governmentId;
        }

        $curl = new Curl(
            url: $this->mapi->getUrl(
                route: sprintf('%s/payments/find_payment/%s', Mapi::PAYMENT_ROUTE, $storeId)
            ),
            requestMethod: RequestMethod::POST,
            payload: $payload,
            contentType: ContentType::JSON,
            authType: AuthType::JWT,
            responseContentType: ContentType::JSON
        );

        $body = $curl->exec()->body;
        $content = (
            $body instanceof stdClass &&
            isset($body->results) &&
            is_array(value: $body->results)
        ) ? $body->results : [];
        $result = DataConverter::arrayToCollection(
            data: $content,
            targetType: Payment::class
        );

        if (!$result instanceof FindPaymentCollection) {
            throw new TypeException(message: 'Expected FindPaymentCollection.');
        }

        return $result;
    }
}

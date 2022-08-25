<?php

namespace Resursbank\Ecom\Module\Payment\Api;

use Resursbank\Ecom\Exception\TypeException;
use Resursbank\Ecom\Lib\Api\Mapi;
use Resursbank\Ecom\Lib\Network\AuthType;
use Resursbank\Ecom\Lib\Network\ContentType;
use Resursbank\Ecom\Lib\Network\Curl;
use Resursbank\Ecom\Lib\Network\RequestMethod;
use Resursbank\Ecom\Lib\Utilities\DataConverter;
use Resursbank\Ecom\Module\Payment\Models\Payment;
use Resursbank\Ecom\Module\Payment\Models\PaymentCollection;
use stdClass;

class GetPayment
{
    /**
     * @param Mapi $mapi
     */
    public function __construct(
        private readonly Mapi $mapi = new Mapi()
    ) {
    }

    public function call(string $orderReference)
    {
        $curl = new Curl(
            url: $this->mapi->getUrl(
                route: sprintf('%s/payments/%s', Mapi::PAYMENT_ROUTE, $orderReference)
            ),
            requestMethod: RequestMethod::GET,
            authType: AuthType::JWT,
            responseContentType: ContentType::JSON
        );

        $data = $curl->exec()->body;

        $content = (
            $data instanceof stdClass
        ) ? $data : new stdClass();

        $result = DataConverter::stdClassToType(
            $content,
            type: Payment::class
        );

        if (!$result instanceof Payment) {
            throw new TypeException(message: 'Expected PaymentCollection.');
        }

        return $result;
    }
}

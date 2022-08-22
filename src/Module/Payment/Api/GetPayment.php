<?php

namespace Resursbank\Ecom\Module\Payment\Api;

use Resursbank\Ecom\Exception\CurlException;
use Resursbank\Ecom\Lib\Api\Mapi;
use Resursbank\Ecom\Lib\Network\AuthType;
use Resursbank\Ecom\Lib\Network\ContentType;
use Resursbank\Ecom\Lib\Network\Curl;
use Resursbank\Ecom\Lib\Network\RequestMethod;

class GetPayment
{
    /**
     * @param Mapi $mapi
     */
    public function __construct(
        private readonly Mapi $mapi = new Mapi()
    ) {
    }

    public function exec(string $orderReference)
    {
        $curl = new Curl(
            url: $this->mapi->getUrl(
                route: sprintf('%s/payments/%s', Mapi::PAYMENT_ROUTE, $orderReference)
            ),
            requestMethod: RequestMethod::GET,
            authType: AuthType::JWT,
            responseContentType: ContentType::JSON
        );

        try {
            $body = $curl->exec()->body;
        } catch (CurlException $e) {
            if ($e->getCode() === 400) {
                // @todo It is most likely that we get a 400-code here, on very various problems.
                // @todo We need to figure out how to handle errors better.


            }
        }
    }
}

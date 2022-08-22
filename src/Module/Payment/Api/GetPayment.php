<?php

namespace Resursbank\Ecom\Module\Payment\Api;

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
                route: sprintf('%s/payments/%s', Mapi::COMMON_ROUTE, $orderReference)
            ),
            requestMethod: RequestMethod::GET,
            authType: AuthType::JWT,
            responseContentType: ContentType::JSON
        );

        $body = $curl->exec()->body;
    }

    private function getUrl()
    {
        $ruwte = $this->mapi->getUrl('/payment');
    }
}

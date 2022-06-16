<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Rco\Models\InitPayment;

use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Module\Rco\Models\MetaDataCollection;
use Resursbank\Ecom\Lib\Network\Curl;

/**
 * Defines a payment request object
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class Request extends Model
{
    public function __construct(
        public OrderLineCollection $orderLines,
        public Customer $customer,
        public string $successUrl,
        public string $backUrl,
        public string $shopUrl,
        public ?string $paymentCreatedCallbackUrl = null,
        public ?MetaDataCollection $metaData = null
    ) {
        parent::__construct();
    }

    public static function initPayment(Request $request): Response
    {
        $curl = new Curl();
        $curl =  $curl->get(
            url: 'https://blablabla.example.com/payments/1234',
            data: $request->toArray()
        );

        $parsedResp = $curl->getParsed();
        return new Response(
            paymentSessionId: $parsedResp->paymentSessionId,
            iframe: $parsedResp->iframe,
            script: $parsedResp->script,
            customer: new Customer(
                governmentId: $parsedResp->customer->governmentId,
                mobile: $parsedResp->customer->mobile,
                email: $parsedResp->customer->email,
                ...
            ),
            baseUrl: $parsedResp->baseUrl,
            html: $parsedResp->html
        );
    }
}

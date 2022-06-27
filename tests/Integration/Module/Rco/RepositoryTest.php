<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Integration\Module\Rco;

use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Lib\Api\Credentials;
use Resursbank\Ecom\Lib\Log\FileLogger;
use Resursbank\Ecom\Lib\Log\LogLevel;
use Resursbank\Ecom\Module\Rco\Models\Address;
use Resursbank\Ecom\Module\Rco\Models\InitPayment\Customer;
use Resursbank\Ecom\Module\Rco\Models\InitPayment\OrderLine;
use Resursbank\Ecom\Module\Rco\Models\InitPayment\OrderLineCollection;
use Resursbank\Ecom\Module\Rco\Models\InitPayment\Request;
use Resursbank\Ecom\Module\Rco\Repository;

final class RepositoryTest extends TestCase
{
    public function testInitPayment(): void
    {
        $request = new Request(
            orderLines: new OrderLineCollection([
                new OrderLine(
                    artNo: "sku123",
                    description: "My product",
                    quantity: 1,
                    unitMeasure: "pc",
                    unitAmountWithoutVat: 20,
                    vatPct: 25
                )
            ]),
            customer: new Customer(
                governmentId: '198305147715',
                mobile: '46701234567',
                email: 'test@hosted.resurs',
                deliveryAddress: new Address(
                    firstName: 'Vincent',
                    lastName: 'Williamsson Alexandersson',
                    addressRow1: 'Glassgatan 15',
                    postalArea: 'Göteborg',
                    postalCode: '41655',
                    countryCode: 'SE'
                )
            ),
            successUrl: 'https://example.com/success',
            backUrl: 'https://example.com/checkout',
            shopUrl: 'https://example.com'
        );
        
        Config::setup(
            credentials: new Credentials('mijase', '4bw4ma1eZfT2KzD7wgWdnTExK0kxmFo2', true),
            logger: new FileLogger(path: '/tmp'),
            logLevel: LogLevel::DEBUG,
            isProduction: false
        );

        $response = Repository::initPayment(
            request: $request,
            orderReference: bin2hex(string: random_bytes(length: 8))
        );
    }
}

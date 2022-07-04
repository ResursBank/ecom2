<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Integration\Module\Rco;

use PHPUnit\Framework\MockObject\MockClass;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Lib\Api\Credentials;
use Resursbank\Ecom\Lib\Log\FileLogger;
use Resursbank\Ecom\Lib\Log\LogLevel;
use Resursbank\Ecom\Lib\Network\Model\Auth\Basic;
use Resursbank\Ecom\Module\Rco\Models\Address;
use Resursbank\Ecom\Module\Rco\Models\InitPayment\Customer;
use Resursbank\Ecom\Module\Rco\Models\InitPayment\OrderLine;
use Resursbank\Ecom\Module\Rco\Models\InitPayment\OrderLineCollection;
use Resursbank\Ecom\Module\Rco\Models\InitPayment\Request;
use Resursbank\Ecom\Module\Rco\Repository;
use Resursbank\Ecom\Module\Rco\Models\UpdatePayment\Request as UpdateRequest;
use Resursbank\Ecom\Module\Rco\Models\UpdatePayment\OrderLine as UpdateOrderLine;
use Resursbank\Ecom\Module\Rco\Models\UpdatePayment\OrderLineCollection as UpdateOrderLineCollection;

final class RepositoryTest extends TestCase
{
    private string $orderReference;
    private Request $request;

    protected function setUp(): void
    {
        $this->orderReference = bin2hex(string: random_bytes(length: 8));
        $this->request = new Request(
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
            logger: $this->createMock(originalClassName: FileLogger::class),
            basicAuth: new Basic(username: 'mijase', password: '4bw4ma1eZfT2KzD7wgWdnTExK0kxmFo2'),
            logLevel: LogLevel::DEBUG,
            isProduction: false
        );
    }

    /**
     * @return void
     * @throws ReflectionException
     */
    public function testInitPayment(): void
    {
        $response = Repository::initPayment(
            request: $this->request,
            orderReference: $this->orderReference
        );

        $this::assertEquals(
            expected: $this->request->customer->governmentId,
            actual: $response->customer->governmentId
        );
        $this::assertEquals(
            expected: '<iframe',
            actual: substr(string: $response->iframe, offset: 0, length: 7)
        );
    }

    public function testUpdatePayment(): void
    {
        $session = Repository::initPayment(
            request: $this->request,
            orderReference: $this->orderReference
        );

        $request = new UpdateRequest(
            orderLines: new UpdateOrderLineCollection(
                data: [
                    new UpdateOrderLine(
                        artNo: 'Updated-1234',
                        description: 'Updated product',
                        quantity: 2,
                        unitMeasure: 'pc',
                        unitAmountWithoutVat: 20,
                        vatPct: 25,
                    )
                ]
            )
        );

        $response = Repository::updatePayment(
            request: $request,
            orderReference: $this->orderReference
        );

        $this::assertEquals(
            expected: 200,
            actual: $response->code
        );
        $this::assertEquals(
            expected: $session->paymentSessionId,
            actual: $response->message
        );
    }

    public function testUpdatePaymentWrongOrderReference(): void
    {
        $session = Repository::initPayment(
            request: $this->request,
            orderReference: $this->orderReference
        );

        $request = new UpdateRequest(
            orderLines: new UpdateOrderLineCollection(
                data: [
                    new UpdateOrderLine(
                        artNo: 'Updated-1234',
                        description: 'Updated product',
                        quantity: 2,
                        unitMeasure: 'pc',
                        unitAmountWithoutVat: 20,
                        vatPct: 25,
                    )
                ]
            )
        );

        $response = Repository::updatePayment(
            request: $request,
            orderReference: $this->orderReference . bin2hex(string: random_bytes(length: 8))
        );

        $this::assertEquals(
            expected: 404,
            actual: $response->code
        );
    }
}

<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Integration\Module\Payment;

use JsonException;
use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\AuthException;
use Resursbank\Ecom\Exception\CurlException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Log\LoggerInterface;
use Resursbank\Ecom\Lib\Network\Model\Auth\Jwt;
use Resursbank\Ecom\Module\Payment\Models\CreatePayment\Order;
use Resursbank\Ecom\Module\Payment\Models\CreatePaymentRequest;
use Resursbank\Ecom\Module\Payment\Models\Payment;
use Resursbank\Ecom\Module\Payment\Repository;

/**
 * Integration tests for CreatePayment repository.
 *
 * @psalm-suppress PropertyNotSetInConstructor
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RepositoryTest extends TestCase
{
    /**
     * @return void
     * @throws EmptyValueException
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    protected function setUp(): void
    {
        Config::setup(
            logger: $this->createMock(originalClassName: LoggerInterface::class),
            jwtAuth: new Jwt(
                clientId: (string) $_ENV['JWT_AUTH_CLIENT_ID'],
                clientSecret: (string) $_ENV['JWT_AUTH_CLIENT_SECRET'],
                scope: (string) $_ENV['JWT_AUTH_SCOPE'],
                grantType: (string) $_ENV['JWT_AUTH_GRANT_TYPE']
            )
        );

        parent::setUp();
    }

    /**
     * Assert read() returns data from the API when cache is empty.
     *
     * @return void
     * @throws AuthException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ValidationException
     */
    public function testCreatePayment(): void
    {
        $result = Repository::createPayment([
            'storeId' => (string) $_ENV['STORE_ID'],
            'paymentMethodId' => (string) $_ENV['PAYMENT_METHOD_ID'],
            'order' => [
                'orderLines' => [
                    [
                        'description' => 'asdasdasd',
                        'quantity' => 2.00,
                        'reference' => 'T-800',
                        'type' => 'PHYSICAL_GOODS',
                        'quantityUnit' => 'st',
                        'unitAmountIncludingVat' => 150.75,
                        'vatRate' => 25.00,
                        'totalAmountIncludingVat' => 301.5,
                        'totalVatAmount' => 60.3
                    ]
                ]
            ]
        ]);

        static::assertInstanceOf(Payment::class, $result);
    }
}

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
        Repository::createPayment(
            json_decode('{
                "storeId": "876aa478-c14e-44a0-b99f-4eb4dcc024c9",
                "paymentMethodId": "07df8603-1fd0-48fc-b17a-cb538ccaede4",
                "order": {
                    "orderLines": [
                        {
                            "description": "Boasdfk",
                            "quantity": 2.00,
                            "reference": "T-800",
                            "type": "PHYSICAL_GOODS",
                            "quantityUnit": "st",
                            "unitAmountIncludingVat": 150.75,
                            "vatRate": 25.00,
                            "totalAmountIncludingVat": 301.5,
                            "totalVatAmount": 60.3
                        }
                    ],
                    "orderReference": "asdfasdfasd"
                },
                "application": {
                    "requestedCreditLimit": 10,
                    "applicationData": {
                        "additionalProp1": "asdfsdasd"
                    }
                },
                "customer": {
                    "contactPerson": "Lorem ipsum",
                    "customerType": "NATURAL",
                    "governmentId": "198305147715",
                    "deliveryAddress": {
                        "fullName": "Vincent Williamsson Alexandersson",
                        "firstName": "Vincent",
                        "lastName": "Alexandersson",
                        "addressRow1": "Glassgatan 15",
                        "postalArea": "Göteborg",
                        "postalCode": "41655",
                        "countryCode": "SE"
                    },
                    "email": "test@resurs.se",
                    "mobilePhone": "+467xyzstuvw",
                    "deviceInfo": {
                        "userAgent": "asdf"
                    }
                },
  "information": {
        "creator": "gert_l",
    "metaData": {
            "key_1": "value_1",
      "key_2": "value_2"
    }
  },
  "metaData": {
        "creator": "asdf asdf asdf asdf asdf asdf asdf asdf asdf asdf "
  },
  "options": {
        "initiatedOnCustomersDevice": true,
    "handleFrozenPayments": true,
    "handleManualInspection": false,
    "callbacks": {
            "authorization": {
                "url": ""
      },
      "management": {
                "url": ""
      }
    },
    "redirectionUrls": {
            "customer": {
                "failUrl": "",
        "successUrl": ""
      },
      "merchant": {
                "failUrl": "",
        "successUrl": ""
      }
    },
    "timeToLiveInMinutes": 1.123
  }
}', true, 512, JSON_THROW_ON_ERROR));
    }
}

<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Integration\Module\Customer;

use JsonException;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Cache\CacheInterface;
use Resursbank\Ecom\Lib\Log\LoggerInterface;
use Resursbank\Ecom\Lib\Model\Address;
use Resursbank\Ecom\Lib\Model\Network\Auth\Jwt;
use Resursbank\Ecom\Lib\Order\CustomerType;
use Resursbank\Ecom\Lib\Utilities\DataConverter;
use Resursbank\Ecom\Module\Customer\Http\GetAddressController as Controller;
use Resursbank\Ecom\Module\Customer\Models\GetAddressRequest;

/**
 * Tests for the API call getAddress.
 */
class GetAddressControllerTest extends TestCase
{
    private Controller $controller;
    private string $storeId;

    /**
     * @return void
     * @throws EmptyValueException
     */
    protected function setUp(): void
    {
        parent::setUp();

        Config::setup(
            logger: $this->createMock(originalClassName: LoggerInterface::class),
            cache: $this->createMock(originalClassName: CacheInterface::class),
            jwtAuth: new Jwt(
                clientId: $_ENV['JWT_AUTH_CLIENT_ID'],
                clientSecret: $_ENV['JWT_AUTH_CLIENT_SECRET'],
                scope: $_ENV['JWT_AUTH_SCOPE'],
                grantType: $_ENV['JWT_AUTH_GRANT_TYPE']
            )
        );

        $this->controller = new Controller();
        $this->storeId = $_ENV['STORE_ID'];
    }

    /**
     * Assert exec() fetches address data.
     *
     * @return void
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     */
    public function testExec(): void
    {
        $data = $this->callController();

        $this->assertResponseContains(needle: 'addressRow1', haystack: $data);

        $obj = json_decode(
            json: $data,
            associative: false,
            depth: 512,
            flags: JSON_THROW_ON_ERROR
        );

        $this->assertIsObject(actual: $obj);

        // Attempt object conversion to ensure we did get an Address back.
        $address = DataConverter::stdClassToType(
            object: $obj,
            type: Address::class
        );

        $this->assertInstanceOf(
            expected: Address::class,
            actual: $address,
            message: 'Failed to convert fetched data to Address instance.'
        );
    }

    /**
     * Assert exec() outputs an error if you attempt to fetch company address
     * with NATURAL customer type specified.
     *
     * @return void
     * @throws EmptyValueException
     * @throws IllegalValueException
     * @throws JsonException
     */
    public function testExecFailure(): void
    {
        $data = $this->callController(govId: '169468958195');

        $this->assertResponseContains(needle: 'error', haystack: $data);

        $obj = json_decode(
            json: $data,
            associative: false,
            depth: 512,
            flags: JSON_THROW_ON_ERROR
        );

        $this->assertIsObject(actual: $obj);

        $this->assertObjectHasAttribute(attributeName: 'error', object: $obj);
        $this->assertNotEmpty(actual: $obj->error);
    }

    /**
     * Simulate calling the controller and getting JSON output.
     *
     * NOTE: This will manipulate headers. This will cause an error since
     * PHPUnit has already set a header. Suppressing is the only way.
     *
     * @param string $govId
     * @param CustomerType $customerType
     *
     * @return string
     * @throws EmptyValueException
     * @throws IllegalValueException
     * @SuppressWarnings(PHPMD.ErrorControlOperator)
     * @noinspection PhpSameParameterValueInspection
     */
    private function callController(
        string $govId = '198001010001',
        CustomerType $customerType = CustomerType::NATURAL
    ): string {
        ob_start();

        /** @noinspection PhpUsageOfSilenceOperatorInspection */
        @$this->controller->exec(
            storeId: $this->storeId,
            data: new GetAddressRequest(
                govId: $govId,
                customerType: $customerType
            )
        );

        return ob_get_clean();
    }

    /**
     * Assert output from controller contains some string. This is an attempt
     * to identify the response before proceeding with further value evaluation.
     *
     * @param string $needle
     * @param string $haystack
     * @return void
     */
    private function assertResponseContains(
        string $needle,
        string $haystack
    ): void {
        $this->assertNotEmpty(actual: $haystack);
        $this->assertStringContainsString(needle: $needle, haystack: $haystack);
    }
}

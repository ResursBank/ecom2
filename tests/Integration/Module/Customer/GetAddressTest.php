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
use Resursbank\Ecom\Exception\ApiException;
use Resursbank\Ecom\Exception\AuthException;
use Resursbank\Ecom\Exception\CacheException;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\CurlException;
use Resursbank\Ecom\Exception\GetAddressException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Cache\CacheInterface;
use Resursbank\Ecom\Lib\Log\LoggerInterface;
use Resursbank\Ecom\Lib\Model\Network\Auth\Jwt;
use Resursbank\Ecom\Lib\Order\CustomerType;
use Resursbank\Ecom\Module\Customer\Repository;
use Resursbank\Ecom\Module\Store\Models\Store;
use Resursbank\Ecom\Module\Store\Repository as StoreRepository;

/**
 * Tests for the API call getAddress.
 */
class GetAddressTest extends TestCase
{
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
    }

    /**
     * @return string
     * @throws AuthException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ValidationException
     * @throws ApiException
     * @throws CacheException
     * @throws IllegalValueException
     */
    private function getStoreId(): string
    {
        $return = $_ENV['STORE_ID'] ?? '';

        /** @var Store $store */
        foreach (StoreRepository::getStores() as $store) {
            if ($store->nationalStoreId === (int)$_ENV['NATIONAL_STORE_ID']) {
                $return = $store->id;
                break;
            }
        }

        return $return;
    }

//    /**
//     * @return string
//     */
//    private function getHappyFlowCustomer(): string
//    {
//        return (string)($_ENV['GOVERNMENT_ID_HAPPY_NATURAL'] ?? '');
//    }
//
//    /**
//     * @return void
//     * @throws AuthException
//     * @throws CurlException
//     * @throws EmptyValueException
//     * @throws GetAddressException
//     * @throws IllegalTypeException
//     * @throws JsonException
//     * @throws ReflectionException
//     * @throws ValidationException
//     */
//    public function testGetAddress(): void
//    {
//        if ($this->isPipeline()) {
//            $this->markTestSkipped(message: 'This test is currently unavailable from pipelines.');
//            return;
//
//        }
//        // Using another "customerIp" so that we can trace requests in central.
//        $_SERVER['REMOTE_ADDR'] = '127.0.0.2';
//
//        $expect = [
//            'fullName' => 'Vincent Williamsson Alexandersson',
//            'addressRow1' => 'Glassgatan 15',
//            'postalArea' => 'Göteborg',
//            'postalCode' => '41655',
//            'countryCode' => 'SE',
//            'firstName' => 'Vincent',
//            'lastName' => 'Alexandersson',
//            'addressRow2' => ''
//        ];
//
//        $address = Repository::getAddress(
//            storeId: $this->getStoreId(),
//            governmentId: $this->getHappyFlowCustomer(),
//            customerType: 'NATURAL'
//        );
//
//        // Testing similarities by intersect.
//        $this->assertCount(expectedCount: 8, haystack: array_intersect((array)$address, $expect));
//    }

    /**
     * @return void
     * @throws ApiException
     * @throws AuthException
     * @throws CacheException
     * @throws ConfigException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws GetAddressException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testGetAddressOliver(): void
    {
        $expect = [
            'fullName' => 'Oliver Liamsson Williamsson',
            'addressRow1' => 'Makadamg 1',
            'postalArea' => 'Helsingborg',
            'postalCode' => '25024',
            'countryCode' => 'SE',
            'firstName' => 'Oliver',
            'lastName' => 'Williamsson',
            'addressRow2' => ''
        ];

        $address = Repository::getAddress(
            storeId: $this->getStoreId(),
            governmentId: '195012026430',
            customerType: CustomerType::NATURAL
        );

        $this->assertEquals(
            expected: $expect,
            actual: $address->toArray(),
            message: 'Fetched address does not match expected result.'
        );
    }

    /**
     * @return void
     * @throws ApiException
     * @throws AuthException
     * @throws CacheException
     * @throws ConfigException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws GetAddressException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testGetAddressOrganization(): void
    {
        $expect = [
            'fullName' => 'Pilsnerbolaget HB',
            'addressRow1' => 'Glassgatan 17',
            'postalArea' => 'Helsingborg',
            'postalCode' => '25024',
            'countryCode' => 'SE',
            'addressRow2' => '',
            'firstName' => null,
            'lastName' => null
        ];

        $address = Repository::getAddress(
            storeId: $this->getStoreId(),
            governmentId: '166997368573',
            customerType: CustomerType::LEGAL
        );

        $this->assertEquals(
            expected: $expect,
            actual: $address->toArray(),
            message: 'Fetched address does not match expected result.'
        );
    }

    /**
     * GetAddress resolving an organization but with NATURAL as customerType.
     *
     * @return void
     * @throws ApiException
     * @throws AuthException
     * @throws CacheException
     * @throws ConfigException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws GetAddressException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testGetBadAddressOrganizationByNatural(): void
    {
        $this->expectException(exception: GetAddressException::class);

        Repository::getAddress(
            storeId: $this->getStoreId(),
            governmentId: '166997368573',
            customerType: CustomerType::NATURAL
        );
    }

    /**
     * GetAddress resolving an organization but with NATURAL as customerType.
     *
     * @return void
     * @throws ApiException
     * @throws AuthException
     * @throws CacheException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws GetAddressException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ValidationException
     * @throws ConfigException
     */
    public function testGetBadAddressByNatural(): void
    {
        $this->expectException(exception: GetAddressException::class);

        Repository::getAddress(
            storeId: $this->getStoreId(),
            governmentId: '8305417715',
            customerType: CustomerType::NATURAL
        );
    }


    /**
     * Assert getAddress with inaccurate SSN results in a CurlException with
     * httpCode 400, morphing to a GetAddressException.
     *
     * @return void
     * @throws ApiException
     * @throws AuthException
     * @throws CacheException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws GetAddressException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ValidationException
     * @throws ConfigException
     */
    public function testInaccurateSsnYieldsHttpCode400(): void
    {
        $this->expectException(exception: GetAddressException::class);

        try {
            Repository::getAddress(
                storeId: $this->getStoreId(),
                governmentId: '1980010100012',
                customerType: CustomerType::NATURAL
            );
        } catch (GetAddressException $e) {
            $curlException = $e->getPrevious();

            if (!$curlException instanceof CurlException) {
                $this->fail(
                    message: 'Expected CurlException to occur before GetAddressException'
                );
            }

            $this->assertSame(
                expected: 400,
                actual: $curlException->httpCode,
                message: "Expected HTTP code 400 got $curlException->httpCode"
            );

            throw $e;
        }
    }

//    /**
//     * @return void
//     * @throws AuthException
//     * @throws CurlException
//     * @throws EmptyValueException
//     * @throws GetAddressException
//     * @throws IllegalTypeException
//     * @throws JsonException
//     * @throws ReflectionException
//     * @throws ValidationException
//     */
//    public function testMismatchAddress(): void
//    {
//        if ($this->isPipeline()) {
//            $this->markTestSkipped(message: 'This test is currently unavailable from pipelines.');
//            return;
//        }
//        $expect = [
//            'fullName' => 'Something Else',
//            'addressRow1' => 'Glassgatan 15',
//            'postalArea' => 'Göteborg',
//            'postalCode' => '41655',
//            'countryCode' => 'SE',
//            'firstName' => 'Vincent',
//            'lastName' => 'Alexandersson',
//            'addressRow2' => ''
//        ];
//
//        $address = Repository::getAddress(
//            storeId: $this->getStoreId(),
//            governmentId: $this->getHappyFlowCustomer(),
//            customerType: 'NATURAL',
//        );
//
//        $this->assertCount(expectedCount: 7, haystack: array_intersect((array)$address, $expect));
//    }
}

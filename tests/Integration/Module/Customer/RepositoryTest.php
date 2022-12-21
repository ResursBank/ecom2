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
use Resursbank\Ecom\Module\Customer\Models\GetAddressRequest;
use Resursbank\Ecom\Module\Customer\Repository;
use Resursbank\Ecom\Module\Store\Models\Store;
use Resursbank\Ecom\Module\Store\Repository as StoreRepository;
use Resursbank\EcomTest\Utilities\MockSessionTrait;

/**
 * Tests for the API call getAddress.
 */
class RepositoryTest extends TestCase
{
    use MockSessionTrait;

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

        $this->setupSession(test: $this);
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
            'addressRow1' => 'Makadamg 13',
            'postalArea' => 'Helsingborg',
            'postalCode' => '25024',
            'countryCode' => 'SE',
            'firstName' => 'Oliver Liamsson',
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

    /**
     * Assert setSsnData() adds data to PHP session.
     *
     * @return void
     * @throws ConfigException
     * @throws JsonException
     */
    public function testSetSsnData(): void
    {
        $this->enableSession();

        $data = new GetAddressRequest(
            govId: '198001010001',
            customerType: CustomerType::NATURAL
        );

        Repository::setSsnData(data: $data, sessionHandler: $this->session);

        $key = $this->session->getKey(key: Repository::SESSION_KEY_SSN_DATA);

        $this->assertTrue(condition: isset($_SESSION));
        $this->assertArrayHasKey(key: $key, array: $_SESSION);

        // To suppress psalm report.
        if (!isset($_SESSION[$key])) {
            $this->fail(message: "$key not set in session.");
        }

        $this->assertSame(
            expected: json_encode(value: $data, flags: JSON_THROW_ON_ERROR),
            actual: $_SESSION[$key]
        );
    }


    /**
     * Assert setSsnData() won't cause an Exception if it cannot store data in
     * PHP session.
     *
     * @return void
     * @throws ConfigException
     */
    public function testSetSsnFailSilentlyWithoutSession(): void
    {
        $this->disableSession();

        $data = new GetAddressRequest(
            govId: '198001010001',
            customerType: CustomerType::NATURAL
        );

        Repository::setSsnData(data: $data, sessionHandler: $this->session);

        $this->assertFalse(condition: isset($_SESSION));
    }

    /**
     * Assert setSsnData() throws ConfigException if Exception cannot be logged.
     *
     * @return void
     * @throws ConfigException
     */
    public function testSetSsnDataThrowsWithoutConfig(): void
    {
        $this->disableSession();

        $data = new GetAddressRequest(
            govId: '198001010001',
            customerType: CustomerType::NATURAL
        );

        $this->expectException(exception: ConfigException::class);

        Config::unsetInstance();

        Repository::setSsnData(data: $data, sessionHandler: $this->session);
    }

    /**
     * Assert getSsnData() returns data stored in session.
     *
     * @return void
     * @throws ConfigException
     */
    public function testGetSsnData(): void
    {
        $this->enableSession();

        $data = new GetAddressRequest(
            govId: '166997368573',
            customerType: CustomerType::LEGAL
        );

        Repository::setSsnData(data: $data, sessionHandler: $this->session);

        $this->assertEquals(
            expected: $data,
            actual: Repository::getSsnData(sessionHandler: $this->session)
        );
    }

    /**
     * Assert getSsnData() returns NULL without data in session.
     *
     * @return void
     * @throws ConfigException
     */
    public function testGetSsnDataReturnsNull(): void
    {
        $this->enableSession();

        $this->assertNull(actual: Repository::getSsnData(sessionHandler: $this->session));
    }

    /**
     * Assert getSsnData() returns NULL when session is disabled.
     *
     * @return void
     * @throws ConfigException
     */
    public function testGetSsnDataReturnsNullWithoutSession(): void
    {
        $this->disableSession();

        $this->assertNull(actual: Repository::getSsnData(sessionHandler: $this->session));
    }

    /**
     * Assert getSsnData() returns NULL if session data is malformed.
     *
     * @return void
     * @throws ConfigException
     */
    public function testGetSsnDataReturnsNullWithMalformedData(): void
    {
        $this->enableSession();

        $key = $this->session->getKey(key: Repository::SESSION_KEY_SSN_DATA);

        // Invalid JSON data.
        $_SESSION[$key] = 'not-json-data';
        $this->assertNull(actual: Repository::getSsnData(sessionHandler: $this->session));

        // Invalid object structure.
        $_SESSION[$key] = '{"harmony":32}';
        $this->assertNull(actual: Repository::getSsnData(sessionHandler: $this->session));

        // Invalid object data.
        $_SESSION[$key] = '{"govId":"166997368573", "customerType":"NATURAL"}';
        $this->assertNull(actual: Repository::getSsnData(sessionHandler: $this->session));
    }

    /**
     * Assert getSsnData() throws ConfigException if Exception cannot be logged.
     *
     * @return void
     * @throws ConfigException
     */
    public function testGetSsnDataThrowsWithoutConfig(): void
    {
        $this->disableSession();
        $this->expectException(exception: ConfigException::class);
        Config::unsetInstance();
        Repository::getSsnData(sessionHandler: $this->session);
    }
}

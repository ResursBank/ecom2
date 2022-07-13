<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Integration\Lib\Network;

use JsonException;
use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\CurlException;
use Resursbank\Ecom\Exception\EmptyException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Api\Credentials;
use Resursbank\Ecom\Lib\Cache\None;
use Resursbank\Ecom\Lib\Log\FileLogger;
use Resursbank\Ecom\Lib\Network\AuthType;
use Resursbank\Ecom\Lib\Network\ContentType;
use Resursbank\Ecom\Lib\Network\Curl;
use Resursbank\Ecom\Lib\Network\Model\Auth\Basic;
use Resursbank\Ecom\Lib\Network\RequestMethod;
use stdClass;

/**
 * This class will test curl methods.
 *
 * @psalm-suppress PropertyNotSetInConstructor
 * @version 1.0.0
 */
class CurlTest extends TestCase
{
    /**
     * @var None $noneCache
     */
    private None $noneCache;

    /**
     * @var Credentials $credentials
     */
    private Credentials $credentials;

    /**
     * @var FileLogger
     */
    private FileLogger $logger;

    /**
     * @var Curl $curl
     */
    private Curl $curl;

    /**
     * Proxy host to test with proxies. On manual tests, you may want to change this host to something
     * that accepts the default HTTP-proxy setup.
     *
     * @var string $proxyHost
     */
    private $proxyHost = '212.63.208.8';

    protected function setUp(): void
    {
        /*$this->noneCache = $this->createMock(
            originalClassName: None::class
        );
        $this->credentials = $this->createMock(
            originalClassName: Credentials::class
        );
        $this->logger = $this->createMock(
            originalClassName: FileLogger::class
        );*/

        parent::setUp();
    }

    public function testNormalAuthentication(): void
    {
        $username = 'testuser';
        $password = 'testpassword';

        Config::setup(
            logger: $this->createMock(originalClassName: FileLogger::class),
            basicAuth: new Basic(username: $username, password: $password)
        );

        $this::assertSame(
            expected: $username,
            actual: Config::$instance->basicAuth->username
        );
        $this::assertSame(
            expected: $password,
            actual: Config::$instance->basicAuth->password
        );
    }

    /**
     * @return bool
     */
    private function isPipeline(): bool
    {
        return isset($_ENV['is_pipeline']) ? (bool)$_ENV['is_pipeline'] : false;
    }

    /**
     * Purpose is to make the curl entity to set credentials automatically from test Config-class.
     * @throws EmptyException
     */
    public function testAuthenticationByConfiguration()
    {
        $username = 'username_config';
        $password = 'password_config';

        Config::setup(
            logger: $this->createMock(originalClassName: FileLogger::class),
            basicAuth: new Basic(username: $username, password: $password)
        );

        $curl = new Curl(
            url: 'https://ipv4.netcurl.org',
            requestMethod: RequestMethod::GET,
            authType: AuthType::BASIC
        );

        $this::assertSame(
            expected: $username,
            actual: $curl->getAuthentication()['username']
        );

        $this::assertSame(
            expected: $password,
            actual: $curl->getAuthentication()['password']
        );
    }

    /**
     * Test to make sure that remote requests really works.
     *
     * @throws CurlException
     * @throws JsonException
     */
    public function testRealGetRequest(): void
    {
        Config::setup(
            logger: $this->createMock(originalClassName: FileLogger::class)
        );
        $curl = new Curl(
            url: 'https://ipv4.netcurl.org',
            requestMethod: RequestMethod::GET,
            contentType: ContentType::URL,
            authType: AuthType::NONE,
            responseContentType: ContentType::JSON
        );
        $response = $curl->exec();

        $this::assertEquals(
            expected: 'GET',
            actual: $response->body->REQUEST_METHOD
        );
        $this->assertSame(
            expected: 200,
            actual: $response->code
        );
    }

    /**
     * Test to make sure that remote requests really works.
     *
     * @return void
     * @throws CurlException
     * @throws JsonException
     * @throws ValidationException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     */
    public function testRealPostRequest(): void
    {
        Config::setup(
            logger: $this->createMock(originalClassName: FileLogger::class)
        );
        $payload = new stdClass();
        $payload->customRow = 'Present';
        $response = Curl::post(
            url: 'https://ipv4.netcurl.org',
            payload: (array)$payload,
            authType: AuthType::NONE
        );

        $this::assertEquals(
            expected: 'POST',
            actual: $response->body->REQUEST_METHOD
        );
        $this::assertEquals(
            expected: $payload,
            actual: json_decode($response->body->input)
        );
    }

    /**
     * @return void
     * @throws CurlException
     * @throws JsonException
     * @throws ValidationException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     */
    public function testRealPutRequest(): void
    {
        Config::setup(
            logger: $this->createMock(originalClassName: FileLogger::class)
        );
        $payload = new stdClass();
        $payload->customRow = 'Present';
        $response = Curl::put(
            url: 'https://ipv4.netcurl.org',
            payload: (array)$payload,
            authType: AuthType::NONE
        );

        $this::assertEquals(
            expected: 'PUT',
            actual: $response->body->REQUEST_METHOD
        );
        $this::assertEquals(
            expected: $payload,
            actual: json_decode($response->body->input)
        );
    }

    /**
     * @return void
     * @throws CurlException
     * @throws JsonException
     * @throws ValidationException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     */
    public function testRealDeleteRequest(): void
    {
        Config::setup(
            logger: $this->createMock(originalClassName: FileLogger::class)
        );
        $response = Curl::delete(
            url: 'https://ipv4.netcurl.org',
            authType: AuthType::NONE
        );

        $this::assertEquals(
            expected: 'DELETE',
            actual: $response->body->REQUEST_METHOD
        );
        $this->assertSame(
            expected: 200,
            actual: $response->code
        );
    }

    /**
     * @test
     */
    public function testTimeout()
    {
        self::expectExceptionCode(28);

        Config::setup(
            logger: $this->createMock(originalClassName: FileLogger::class),
            timeout: 1
        );

        // We need to move those features "in house" at some point (like timeout.resurs.com).
        $curl = new Curl(
            url: 'https://timeout.netcurl.org',
            requestMethod: RequestMethod::GET,
            authType: AuthType::NONE
        );

        // Default for requests to "timeout.netcurl.org" is that it responds after a timeout of 10 seconds.
        $curl->exec();
    }

    /**
     * @test
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws JsonException
     * @throws ValidationException
     */
    public function testProxy()
    {
        if ($this->isPipeline()) {
            self::markTestSkipped('Pipelines does not support proxies.');
            return;
        }

        Config::setup(
            logger: $this->createMock(originalClassName: FileLogger::class),
            proxy: sprintf('%s:80', $this->proxyHost)
        );

        $response = Curl::get(
            url: 'https://ipv4.netcurl.org',
            authType: AuthType::NONE
        );

        // Request should reflect the proxy ip, not your own.
        self::assertSame(
            $this->proxyHost,
            $response->body->ip
        );
    }

    /**
     * Verify that CurlException for 404 pages has code set to 404
     * 
     * @return void
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws JsonException
     * @throws ValidationException
     */
    public function testFileNotFound(): void
    {
        $this->expectExceptionCode(code:404);

        Config::setup(
            logger: $this->createMock(originalClassName: FileLogger::class)
        );

        Curl::get(
            url: 'https://ipv4.netcurl.org/http.php?code=404',
            authType: AuthType::NONE
        );
    }

    /**
     * @param $class
     * @return mixed|string
     */
    private function getNamespaceClass($class)
    {
        $return = '';

        $wrapperClassExplode = explode('\\', $class);
        if (is_array($wrapperClassExplode) && count($wrapperClassExplode)) {
            $return = $wrapperClassExplode[count($wrapperClassExplode) - 1];
        }

        return $return;
    }
}

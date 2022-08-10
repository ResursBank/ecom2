<?php

/** @noinspection PsalmGlobal */

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Integration\Lib\Network;

use JsonException;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\AuthException;
use Resursbank\Ecom\Exception\CurlException;
use Resursbank\Ecom\Exception\TypeException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Log\FileLogger;
use Resursbank\Ecom\Lib\Network\AuthType;
use Resursbank\Ecom\Lib\Network\ContentType;
use Resursbank\Ecom\Lib\Network\Curl;
use Resursbank\Ecom\Lib\Network\Model\Auth\Basic;
use Resursbank\Ecom\Lib\Network\RequestMethod;
use Resursbank\Ecom\Lib\Utilities\Generic;
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
     * Proxy host to test with proxies. On manual tests, you may want to change this host to something
     * that accepts the default HTTP-proxy setup.
     *
     * @var string $proxyHost
     */
    private string $proxyHost = '212.63.208.8';

    /**
     * Almost-random proxy ip to test prohibited requests.
     *
     * @var string $badProxyHost
     */
    private string $badProxyHost = '95.216.170.246';

    /**
     * The server at 95.216.170.246 throws a HTTP 400 rather than 403 since the remote is a non-proxy nginx setup.
     * If you ever change the $badProxyHost, make sure you match the errors returned from the server by changing
     * this value.
     *
     * @var int $expectBadProxyStatusCode
     */
    private int $expectBadProxyStatusCode = 400;

    /**
     * Verify that Basic auth properties are set when creating a Basic auth instance
     *
     * @return void
     * @throws EmptyValueException
     */
    public function testNormalAuthentication(): void
    {
        $username = 'user';
        $password = 'password';

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
        return isset($_ENV['is_pipeline']) && $_ENV['is_pipeline'];
    }

    /**
     * Purpose is to make the curl entity to set credentials automatically from test Config-class.
     * @throws EmptyValueException
     */
    public function testAuthenticationByConfiguration(): void
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
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws JsonException
     */
    public function testRealGetRequest(): void
    {
        Config::setup(
            logger: $this->createMock(originalClassName: FileLogger::class),
            userAgent: self::class
        );

        $curl = new Curl(
            url: 'https://ipv4.netcurl.org',
            requestMethod: RequestMethod::GET,
            contentType: ContentType::URL,
            authType: AuthType::NONE,
            responseContentType: ContentType::JSON,
        );
        $response = $curl->exec();

        $this->assertTrue(
            strpos($response->body->HTTP_USER_AGENT, self::class) === 0
        );

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
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws JsonException
     * @throws ReflectionException
     */
    public function testRealGetRequestWithCustomUserAgent(): void
    {
        $expectRemoteVersion = 'EComTest-Custom-' . (new Generic())->getVersionByClassDoc(self::class);

        Config::setup(
            logger: $this->createMock(originalClassName: FileLogger::class),
            userAgent: $expectRemoteVersion
        );

        $curl = new Curl(
            url: 'https://ipv4.netcurl.org',
            requestMethod: RequestMethod::GET,
            contentType: ContentType::URL,
            authType: AuthType::NONE,
            responseContentType: ContentType::JSON,
        );
        $response = $curl->exec();

        $this->assertTrue(
            strpos($response->body->HTTP_USER_AGENT, $expectRemoteVersion) === 0
        );

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
     * @throws AuthException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws JsonException
     * @throws TypeException
     * @throws ValidationException
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
            actual: json_decode(
                json: $response->body->input,
                associative: false,
                depth: 32,
                flags: JSON_THROW_ON_ERROR
            )
        );
    }

    /**
     * @return void
     * @throws AuthException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws JsonException
     * @throws TypeException
     * @throws ValidationException
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
            actual: json_decode(
                json: $response->body->input,
                associative: false,
                depth: 16,
                flags: JSON_THROW_ON_ERROR
            )
        );
    }

    /**
     * @return void
     * @throws AuthException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws JsonException
     * @throws TypeException
     * @throws ValidationException
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
     * @return void
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws JsonException
     * @noinspection SpellCheckingInspection
     */
    public function testTimeout(): void
    {
        $this->expectExceptionCode(28);

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
     * Verify that proxy connections work
     *
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws JsonException
     */
    public function testProxy(): void
    {
        if ($this->isPipeline()) {
            self::markTestSkipped('Pipelines does not support proxies.');
        }

        Config::setup(
            logger: $this->createMock(originalClassName: FileLogger::class),
            proxy: sprintf('%s:80', $this->proxyHost)
        );

        // We need to move those features "in house" at some point (like timeout.resurs.com).
        $curl = new Curl(
            url: 'https://ipv4.netcurl.org',
            requestMethod: RequestMethod::GET,
            authType: AuthType::NONE,
            responseContentType: ContentType::JSON
        );

        try {
            $response = $curl->exec();

            // Request should reflect the proxy ip, not your own.
            self::assertSame(
                $this->proxyHost,
                $response->body->ip
            );
        } catch (CurlException $e) {
            $this->markTestSkipped(
                sprintf(
                    'Can not run proxy test! Caught error (%d) from remote server: %s.',
                    $e->getCode(),
                    $e->getMessage()
                )
            );
        }
    }

    /**
     * Verify that proxy connections work
     *
     * @throws AuthException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws JsonException
     * @throws TypeException
     * @throws ValidationException
     */
    public function testBadProxy(): void
    {
        $this->expectExceptionCode(code: $this->expectBadProxyStatusCode);

        Config::setup(
            logger: $this->createMock(originalClassName: FileLogger::class),
            proxy: sprintf('%s:80', $this->badProxyHost)
        );

        Curl::get(
            url: 'https://ipv4.netcurl.org',
            authType: AuthType::NONE
        );
    }

    /**
     * Verify that CurlException for 404 pages has code set to 404
     *
     * @return void
     * @throws AuthException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws JsonException
     * @throws TypeException
     * @throws ValidationException
     */
    public function testFileNotFound(): void
    {
        $this->expectExceptionCode(code: 404);

        Config::setup(
            logger: $this->createMock(originalClassName: FileLogger::class)
        );

        Curl::get(
            url: 'https://ipv4.netcurl.org/http.php?code=404',
            authType: AuthType::NONE
        );
    }

    /**
     * @return void
     * @throws AuthException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws JsonException
     * @throws TypeException
     * @throws ValidationException
     */
    public function testPermissionDenied(): void
    {
        $this->expectExceptionCode(code: 403);

        Config::setup(
            logger: $this->createMock(originalClassName: FileLogger::class)
        );

        Curl::get(
            url: 'https://ipv4.netcurl.org/http.php?code=403',
            authType: AuthType::NONE
        );
    }
}

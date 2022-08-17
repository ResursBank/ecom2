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
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Log\FileLogger;
use Resursbank\Ecom\Lib\Network\AuthType;
use Resursbank\Ecom\Lib\Network\ContentType;
use Resursbank\Ecom\Lib\Network\Curl;
use Resursbank\Ecom\Lib\Network\Model\Auth\Basic;
use Resursbank\Ecom\Lib\Network\Model\Response;
use Resursbank\Ecom\Lib\Network\RequestMethod;
use Resursbank\Ecom\Lib\Utilities\Generic;
use stdClass;

use function is_string;

/**
 * This class will test curl methods.
 *
 * @psalm-suppress PropertyNotSetInConstructor
 * @version 1.0.0
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
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
     * The server at 95.216.170.246 throws an HTTP 400 rather than 403 since the remote is a non-proxy nginx setup.
     * If you ever change the $badProxyHost, make sure you match the errors returned from the server by changing
     * this value.
     *
     * @var int $expectBadProxyStatusCode
     */
    private int $expectBadProxyStatusCode = 400;

    /**
     * @param Response $response
     * @return stdClass
     */
    private function getRequestBodyObject(
        Response $response
    ): stdClass {
        if (!($response->body instanceof stdClass)) {
            self::fail(message: 'Response body is not an object.');
        }

        return $response->body;
    }

    /**
     * @param Response $response
     * @param string $expected
     * @return void
     */
    private function validateRequestMethod(
        Response $response,
        string $expected
    ): void {
        $body = $this->getRequestBodyObject(response: $response);

        if (!isset($body->REQUEST_METHOD)) {
            self::fail(message: 'No REQUEST_METHOD found in response body.');
        }

        self::assertSame(
            expected: $expected,
            actual: $body->REQUEST_METHOD
        );
    }

    /**
     * @param Response $response
     * @param string $startsWith
     * @return void
     */
    private function validateUserAgent(
        Response $response,
        string $startsWith
    ): void {
        $body = $this->getRequestBodyObject(response: $response);

        if (!isset($body->HTTP_USER_AGENT)) {
            self::fail(message: 'No HTTP_USER_AGENT found in response body.');
        }

        if (!is_string(value: $body->HTTP_USER_AGENT)) {
            self::fail(
                message: 'HTTP_USER_AGENT in response body is not a string.'
            );
        }

        self::assertTrue(
            condition: str_starts_with(
                haystack: $body->HTTP_USER_AGENT,
                needle: $startsWith
            )
        );
    }

    /**
     * @param Response $response
     * @return string
     */
    private function getInput(
        Response $response
    ): string {
        $body = $this->getRequestBodyObject(response: $response);

        if (!isset($body->input)) {
            self::fail(message: 'No input found in response body.');
        }

        if (!is_string(value: $body->input)) {
            self::fail(
                message: 'input in response body is not a string.'
            );
        }

        return $body->input;
    }

    /**
     * @param Response $response
     * @return string
     */
    private function getIp(
        Response $response
    ): string {
        $body = $this->getRequestBodyObject(response: $response);

        if (!isset($body->ip)) {
            self::fail(message: 'No ip found in response body.');
        }

        if (!is_string(value: $body->ip)) {
            self::fail(
                message: 'ip in response body is not a string.'
            );
        }

        return $body->ip;
    }

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

        if (Config::$instance->basicAuth === null) {
            self::fail(message: 'Basic auth is not set.');
        }

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
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    private function isPipeline(): bool
    {
        return (
            isset($_ENV['is_pipeline']) &&
            (bool) $_ENV['is_pipeline'] === true
        );
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

        $this->validateUserAgent(response: $response, startsWith: self::class);
        $this->validateRequestMethod(response: $response, expected: 'GET');

        self::assertSame(
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
        $expectRemoteVersion = 'EComTest-Custom-' .
            (new Generic())->getVersionByClassDoc(className: self::class);

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

        $this->validateUserAgent(
            response: $response,
            startsWith: $expectRemoteVersion
        );
        $this->validateRequestMethod(response: $response, expected: 'GET');

        self::assertSame(
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

        $this->validateRequestMethod(response: $response, expected: 'POST');

        self::assertEquals(
            expected: $payload,
            actual: json_decode(
                json: $this->getInput(response: $response),
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

        $this->validateRequestMethod(response: $response, expected: 'PUT');

        self::assertEquals(
            expected: $payload,
            actual: json_decode(
                json: $this->getInput(response: $response),
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

        $this->validateRequestMethod(response: $response, expected: 'DELETE');

        self::assertSame(
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
        $this->expectExceptionCode(code: 28);

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
            self::markTestSkipped(
                message: 'Pipelines does not support proxies.'
            );
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
                expected: $this->proxyHost,
                actual: $this->getIp(response: $response),
            );
        } catch (CurlException $e) {
            self::markTestSkipped(
                message: sprintf(
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
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws JsonException
     * @throws ValidationException
     */
    public function testBadProxy(): void
    {
        $this->expectExceptionCode(code: $this->expectBadProxyStatusCode);

        Config::setup(
            logger: $this->createMock(originalClassName: FileLogger::class),
            proxy: sprintf('%s:80', $this->badProxyHost)
        );

        try {
            Curl::get(
                url: 'https://ipv4.netcurl.org',
                authType: AuthType::NONE
            );
        } catch (CurlException $e) {
            self::markTestSkipped(
                message: sprintf(
                    'Can not run proxy test! Caught error (%d) from remote server: %s.',
                    $e->getCode(),
                    $e->getMessage()
                )
            );
        }
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

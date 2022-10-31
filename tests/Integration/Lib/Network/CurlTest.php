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
            $this->fail(message: 'Response body is not an object.');
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
            $this->fail(message: 'No REQUEST_METHOD found in response body.');
        }

        $this->assertSame(
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
            $this->fail(message: 'No HTTP_USER_AGENT found in response body.');
        }

        if (!is_string(value: $body->HTTP_USER_AGENT)) {
            $this->fail(
                message: 'HTTP_USER_AGENT in response body is not a string.'
            );
        }

        $this->assertTrue(
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
            $this->fail(message: 'No input found in response body.');
        }

        if (!is_string(value: $body->input)) {
            $this->fail(
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
            $this->fail(message: 'No ip found in response body.');
        }

        if (!is_string(value: $body->ip)) {
            $this->fail(
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
            $this->fail(message: 'Basic auth is not set.');
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

        $this->assertEquals(
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

        $this->assertEquals(
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

        $this->assertSame(
            expected: 200,
            actual: $response->code
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
     * @noinspection SpellCheckingInspection
     */
    public function testTimeout(): void
    {
        //$this->expectExceptionCode(code: 28);
        Config::setup(
            logger: $this->createMock(originalClassName: FileLogger::class),
            timeout: 2
        );

        $timeoutUrl = 'https://timeout.netcurl.org';

        // We need to move those features "in house" at some point (like timeout.resurs.com).
        $curl = new Curl(
            url: $timeoutUrl,
            requestMethod: RequestMethod::GET,
            authType: AuthType::NONE
        );

        try {
            // Default for requests to "timeout.netcurl.org" is that it responds after a timeout of 10 seconds.
            $curl->exec();
        } catch (CurlException $e) {
            if ($e->getCode() !== 28) {
                static::markTestSkipped(
                    message: sprintf(
                        'Problems occured with %s (error %s: %s).',
                        $timeoutUrl,
                        $e->getCode(),
                        $e->getMessage()
                    )
                );
            } else {
                static::assertSame(expected: 28, actual: $e->getCode());
            }
        }
    }

    /**
     * Verify that proxy connections work
     *
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws JsonException
     * @SuppressWarnings(PHPMD.superGlobals)
     */
    public function testProxy(): void
    {
        if ((bool) $_ENV['SKIP_PROXY_TESTS']) {
            static::markTestSkipped(
                message: 'Skipping proxy tests because of environment variable.'
            );
        }

        if ((bool) $_ENV['IS_PIPELINE']) {
            $this->markTestSkipped(message: 'Pipeline does not support proxies.');
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
            $this->assertSame(
                expected: $this->proxyHost,
                actual: $this->getIp(response: $response),
            );
        } catch (CurlException $e) {
            $this->markTestSkipped(
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
            $this->markTestSkipped(
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

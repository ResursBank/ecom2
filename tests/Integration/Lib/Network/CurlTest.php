<?php

declare(strict_types=1);

namespace Resursbank\EcomTest\Integration\Lib\Network;

use stdClass;
use JsonException;
use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\CurlException;
use Resursbank\Ecom\Exception\EmptyException;
use Resursbank\Ecom\Lib\Api\Credentials;
use Resursbank\Ecom\Lib\Cache\None;
use Resursbank\Ecom\Lib\Log\FileLogger;
use Resursbank\Ecom\Lib\Network\AuthType;
use Resursbank\Ecom\Lib\Network\Curl;
use Resursbank\Ecom\Lib\Network\ContentType;
use Resursbank\Ecom\Lib\Network\Model\Auth\Basic;
use Resursbank\Ecom\Lib\Network\RequestMethod;

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
    public function testRealGetRequest() : void
    {
        Config::setup(
            logger: $this->createMock(originalClassName: FileLogger::class)
        );
        $response = Curl::get(
            url: 'https://ipv4.netcurl.org',
            authType: AuthType::NONE
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
     * @throws JsonException
     * @return void
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
     * @throws \Resursbank\Ecom\Exception\ValidationException
     * @throws \Resursbank\Ecom\Exception\Validation\EmptyValueException
     * @throws \Resursbank\Ecom\Exception\Validation\IllegalTypeException
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
     * @throws \Resursbank\Ecom\Exception\ValidationException
     * @throws \Resursbank\Ecom\Exception\Validation\EmptyValueException
     * @throws \Resursbank\Ecom\Exception\Validation\IllegalTypeException
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
            logger: $this->createMock(originalClassName: FileLogger::class)
        );

        $curl = new Curl(
            url: 'https://timeout.netcurl.org',
            requestMethod: RequestMethod::GET,
            authType: AuthType::NONE
        );
        $curl->setTimeout(1);
        // Default for requests to the site below is that it has a response timeout for 10 sec.
        // We need to move those features "in house" at some point.
        $curl->exec();
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

<?php

declare(strict_types=1);

namespace Resursbank\EcomTest\Lib\Network;

use JsonException;
use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\CurlException;
use Resursbank\Ecom\Exception\EmptyException;
use Resursbank\Ecom\Lib\Api\Credentials;
use Resursbank\Ecom\Lib\Cache\None;
use Resursbank\Ecom\Lib\Log\FileLogger;
use Resursbank\Ecom\Lib\Network\Curl;
use Resursbank\Ecom\Lib\Network\DataType;

/**
 * This class will test curl methods.
 *
 * @psalm-suppress PropertyNotSetInConstructor
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

    public function testNormalAuthentication()
    {
        $un = 'testuser';
        $pw = 'testpassword';

        $this->curl->setAuthentication($un, $pw);

        self::assertSame($un, $this->curl->getAuthentication()['username']);
        self::assertSame($pw, $this->curl->getAuthentication()['password']);
    }

    /**
     * Purpose is to make the curl entity to set credentials automatically from test Config-class.
     * @throws EmptyException
     */
    public function testAuthenticationByConfiguration()
    {
        $un = 'username_config';
        $pw = 'password_config';

        Config::setup(
            credentials: new Credentials(username: $un, password: $pw, test: true),
            logger: $this->logger
        );

        self::assertSame($un, $this->curl->getAuthentication()['username']);
        self::assertSame($pw, $this->curl->getAuthentication()['password']);
    }

    /**
     * Test to make sure that remote requests really works.
     *
     * @throws CurlException
     * @throws JsonException
     */
    public function testRealGetRequest()
    {
        $curlRequest = $this->curl->get('https://ipv4.netcurl.org');
        self::assertTrue(
            $this->validateRemoteAddr(
                $curlRequest->getParsed()->ip
            ) && $curlRequest->getCode() === 200
        );
    }

    /**
     * @param $ip
     * @return mixed
     */
    private function validateRemoteAddr($ip)
    {
        return filter_var($ip, FILTER_VALIDATE_IP) === $ip;
    }

    /**
     * Test to make sure that remote requests really works.
     *
     * @throws CurlException
     * @throws JsonException
     */
    public function testRealPostRequest()
    {
        $customPostRow = '{"customRow":"Present"}';
        $curlRequestJson = $this->curl->post('https://ipv4.netcurl.org', ['customRow' => 'Present']);

        // As we use the same curl-session here, it is important that we fetch the
        // input data before making next request.
        $jsonInput = $curlRequestJson->getParsed()->input;

        $curlRequestPostGet = $this->curl->post(
            'https://ipv4.netcurl.org',
            ['customRow' => 'Present'],
            DataType::POSTVARS
        );

        self::assertSame($customPostRow, $jsonInput);
        self::assertTrue(
            isset($curlRequestPostGet->getParsed()->PARAMS_REQUEST->customRow) &&
            $curlRequestPostGet->getParsed()->PARAMS_REQUEST->customRow === 'Present'
        );
    }

    protected function setUp(): void
    {
        $this->noneCache = $this->createMock(
            originalClassName: None::class
        );
        $this->credentials = $this->createMock(
            originalClassName: Credentials::class
        );
        $this->logger = $this->createMock(
            originalClassName: FileLogger::class
        );

        $this->curl = new Curl();

        Config::setup(
            credentials: $this->credentials,
            logger: $this->logger,
            userAgent: $this->getNamespaceClass(self::class)
        );

        parent::setUp();
    }

    /**
     * Testing tokens and making sure this is set on remote ends.
     *
     * @throws CurlException
     * @throws JsonException
     */
    public function testSetToken()
    {
        $tokenString = 'Bearer 4b8b4bfdc6de0033ef5c42ca439b572867229556';
        $this->curl->setTokenBearer(sha1('this_bearer'));
        self::assertSame(
            $tokenString,
            $this->curl->get('https://ipv4.netcurl.org')->getParsed()->HTTP_AUTHORIZATION
        );
    }

    /**
     * @test
     */
    public function testTimeout()
    {
        self::expectExceptionCode(28);
        $this->curl->setTimeout(3);
        // Default for requests to the site below is that it has a response timeout for 10 sec.
        // We need to move those features "in house" at some point.
        $this->curl->get('https://timeout.netcurl.org/');
    }

    /**
     * @return bool
     */
    private function isPipeline(): bool
    {
        return isset($_ENV['is_pipeline']) ? (bool)$_ENV['is_pipeline'] : false;
    }

    /**
     * Strict proxy testing. Requires access to either 212.63.208.8 or another
     *
     * @test
     */
    public function setProxy()
    {
        if ($this->isPipeline()) {
            self::markTestSkipped('Pipelines does not support proxies.');
            return;
        }

        Config::setup(
            credentials: new Credentials(username: 'no', password: 'no', test: true),
            logger: $this->logger,
            proxy: sprintf('%s:80', $this->proxyHost)
        );

        $request = $this->curl->get('https://ipv4.netcurl.org');

        // Request should reflect the proxy ip, not your own.
        self::assertSame(
            $this->proxyHost,
            $request->getParsed()->ip
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

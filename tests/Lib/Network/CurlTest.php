<?php

declare(strict_types=1);

namespace Resursbank\EcomTest\Lib\Network;

use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\EmptyException;
use Resursbank\Ecom\Lib\Api\Credentials;
use Resursbank\Ecom\Lib\Cache\None;
use Resursbank\Ecom\Lib\Log\FileLogger;
use Resursbank\Ecom\Lib\Network\Curl;

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

        /*$this->curl = $this->createMock(
            originalClassName: Curl::class
        );*/
        $this->curl = new Curl();

        Config::setup(
            credentials: $this->credentials,
            logger: $this->logger
        );

        parent::setUp();
    }

    public function testNormalAuthentication()
    {
        $un = 'testuser';
        $pw = 'testpassword';

        $this->credentials->method('getUsername')->willReturn($un);
        $this->credentials->method('getPassword')->willReturn($pw);
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

    public function testGet()
    {
        $test = $this->curl->get('https://ipv4.netcurl.org');
        print_R($test);
    }
}

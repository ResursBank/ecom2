<?php

declare(strict_types=1);

namespace Resursbank\EcomTest\Lib\Network;

use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Lib\Network\Curl;

/**
 * This class will test curl methods.
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
class CurlTest extends TestCase
{
    private Curl $curl;

    protected function setUp(): void
    {
        $this->curl = $this->getMockForAbstractClass(
            originalClassName: Curl::class
        );

        /*Config::setup(
            credentials: $credentials
        );*/
        parent::setUp();
    }

    public function testAuth()
    {
        $this->curl->setAuthentication('testuser', 'testpassword');
        self::assertSame('testuser', $this->curl->getAuthentication()['username']);
        self::assertSame('testpassword', $this->curl->getAuthentication()['password']);
    }

    public function testGet()
    {
        $test = $this->curl->get('https://ipv4.netcurl.org');
        print_R($test);
    }
}

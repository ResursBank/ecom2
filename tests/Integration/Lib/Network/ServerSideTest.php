<?php

declare(strict_types=1);

namespace Resursbank\EcomTest\Integration\Lib\Network;

use JsonException;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Resursbank\Ecom\Exception\AttributeCombinationException;
use Resursbank\Ecom\Lib\Model\Payment\Customer\DeviceInfo;
use Resursbank\Ecom\Lib\Network\Server;

class ServerSideTest extends TestCase
{
    protected string $ip4normal = '192.168.17.43';

    private string $ipEmpty = '';

    private string $ipBad = 'I.Am.Bad.IP';

    private string $strangeCloudflare = '1.2.3.4, 1.2.3.4, 1.2.3.4';

    private string $ipV6long = '2001:460:FFFF:AAAA:BBBB:CCCC:DDDD:EEEE';

    private string $ipV6short = '2001:460:FFFF::999';

    private string $ipv6Invalid = '2001:460:FFFF:AAAA:BBBB:CCCC:DDDD:OOPS:FFFF';

    /**
     * 192.168.10.1, as a decimal value.
     */
    private string $ipDecimal = '3232238081';

    /**
     * The local link should actually not be possible to get in a REMOTE_ADDR, but it is still a valid ip.
     */
    private string $ip6LocalLink = 'fe80::';

    /**
     * @throws JsonException
     * @throws ReflectionException
     * @throws AttributeCombinationException
     */
    public function testDeviceInfoFullDefaultIp(): void
    {
        $deviceInfo = new DeviceInfo(
            ip: $this->ip4normal,
            userAgent: 'UA Plugin 1.0.0'
        );

        $this->assertEquals(
            expected: $this->ip4normal,
            actual: $deviceInfo->ip
        );
    }

    /**
     * @throws AttributeCombinationException
     * @throws JsonException
     * @throws ReflectionException
     */
    public function testDeviceInfoIpEmpty(): void
    {
        $deviceInfo = new DeviceInfo(ip: $this->ipEmpty);

        $this->assertEquals(expected: '', actual: $deviceInfo->ip);
    }

    public function testDeviceInfoIpNull(): void
    {
        $deviceInfo = new DeviceInfo();

        $this->assertEquals(expected: null, actual: $deviceInfo->ip);
    }

    public function testIpCheckBad(): void
    {
        $this->assertNull(actual: Server::getValidatedIp(ip: $this->ipBad));
    }

    public function testIpCheckEmpty(): void
    {
        $this->assertNull(actual: Server::getValidatedIp(ip: $this->ipEmpty));
    }

    public function testIpCheckNull(): void
    {
        $this->assertNull(actual: Server::getValidatedIp(ip: $this->ipEmpty));
    }

    /**
     * Expect null returns when we receive a string with multiple ip's.
     */
    public function testIpCheckMultipleCloudy(): void
    {
        $this->assertNull(
            actual: Server::getValidatedIp(ip: $this->strangeCloudflare)
        );
    }

    public function testIpCheckV4default(): void
    {
        $this->assertSame(
            expected: $this->ip4normal,
            actual: Server::getValidatedIp(ip: $this->ip4normal)
        );
    }

    public function testIpCheckV6NetShort(): void
    {
        $this->assertSame(
            expected: $this->ipV6short,
            actual: Server::getValidatedIp(ip: $this->ipV6short)
        );
    }

    public function testIpCheckV6NetLong(): void
    {
        $this->assertSame(
            expected: $this->ipV6long,
            actual: Server::getValidatedIp(ip: $this->ipV6long)
        );
    }

    public function testIpCheckV6Local(): void
    {
        $this->assertSame(
            expected: $this->ip6LocalLink,
            actual: Server::getValidatedIp(ip: $this->ip6LocalLink)
        );
    }

    public function testIpCheckV6Invalid(): void
    {
        $this->assertNull(
            actual: Server::getValidatedIp(ip: $this->ipv6Invalid)
        );
    }

    /**
     * Decimal ip's are expected to not be converted anywhere, so this should be null.
     */
    public function testIpCheckDecimal(): void
    {
        $this->assertNull(actual: Server::getValidatedIp(ip: $this->ipDecimal));
    }

    /**
     * Spoof and expect a specific user agent.
     */
    public function testDeviceUserAgent(): void
    {
        $_SERVER['HTTP_USER_AGENT'] = 'WooCommerce for Magento v1.0.0';
        $this->assertSame(
            expected: $_SERVER['HTTP_USER_AGENT'],
            actual: Server::getUserAgent()
        );
        // Unset after assertion.
        unset($_SERVER['HTTP_USER_AGENT']);
    }
}

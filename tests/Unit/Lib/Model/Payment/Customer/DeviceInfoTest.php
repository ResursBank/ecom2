<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Lib\Model\Payment\Customer;

use JsonException;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Resursbank\Ecom\Exception\AttributeCombinationException;
use Resursbank\Ecom\Exception\Validation\IllegalIpException;
use Resursbank\Ecom\Lib\Model\Payment\Customer\DeviceInfo;

class DeviceInfoTest extends TestCase
{
    protected string $ip4normal = '192.168.17.43';

    private string $ipBad = 'I.Am.Bad.IP';

    private string $strangeCloudflare = '1.2.3.4, 1.2.3.4, 1.2.3.4';

    private string $ipv6long = '2001:460:FFFF:AAAA:BBBB:CCCC:DDDD:EEEE';

    private string $ipv6short = '2001:460:FFFF::999';

    private string $ipv6Invalid = '2001:460:FFFF:AAAA:BBBB:CCCC:DDDD:OOPS:FFFF';

    /**
     * 192.168.10.1, as a decimal value.
     */
    private string $ipDecimal = '3232238081';

    /**
     * The local link should actually not be possible to get in a REMOTE_ADDR, but it is still a valid ip.
     */
    private string $ipv6LocalLink = 'fe80::';

    protected function setUp(): void
    {
        parent::setUp();
        unset($_SERVER['REMOTE_ADDR']);
    }

    private function setClientIpAddress(?string $ipAddress): void
    {
        $_SERVER['REMOTE_ADDR'] = $ipAddress;
    }

    /**
     * @throws JsonException
     * @throws ReflectionException
     * @throws AttributeCombinationException
     */
    public function testDeviceInfoFullDefaultIp(): void
    {
        $this->setClientIpAddress(ipAddress: $this->ip4normal);
        $deviceInfo = new DeviceInfo(
            ip: DeviceInfo::getIp(),
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
        $this->setClientIpAddress(ipAddress: '');
        $deviceInfo = new DeviceInfo(ip: DeviceInfo::getIp());
        $this->assertEquals(expected: '', actual: $deviceInfo->ip);
    }

    public function testDeviceInfoIpNull(): void
    {
        $this->setClientIpAddress(ipAddress: null);
        $this->assertEquals(expected: null, actual: (new DeviceInfo())->ip);
    }

    /**
     * @throws AttributeCombinationException
     * @throws JsonException
     * @throws ReflectionException
     */
    public function testGetIpCheckBad(): void
    {
        $this->setClientIpAddress(ipAddress: $this->ipBad);
        $this->assertNull(
            actual: (new DeviceInfo(ip: DeviceInfo::getIp()))->ip
        );
    }

    /**
     * Test setting bad ip address directly in DeviceInfo.
     *
     * @throws AttributeCombinationException
     * @throws JsonException
     * @throws ReflectionException
     */
    public function testDeviceInfoBadIp(): void
    {
        $this->setClientIpAddress(ipAddress: $this->ipBad);
        $this->expectException(exception: IllegalIpException::class);
        new DeviceInfo(ip: $this->ipBad);
    }

    /**
     * Test setting bad ip address directly in DeviceInfo.
     *
     * @throws AttributeCombinationException
     * @throws JsonException
     * @throws ReflectionException
     */
    public function testDeviceInfoBadIp6(): void
    {
        $this->setClientIpAddress(ipAddress: $this->ipv6Invalid);
        $this->expectException(exception: IllegalIpException::class);
        new DeviceInfo(ip: $this->ipBad);
    }

    /**
     * @throws AttributeCombinationException
     * @throws JsonException
     * @throws ReflectionException
     */
    public function testGetIpCheckEmpty(): void
    {
        $this->setClientIpAddress(ipAddress: '');
        $this->assertNull(
            actual: (new DeviceInfo(ip: DeviceInfo::getIp()))->ip
        );
    }

    /**
     * @throws AttributeCombinationException
     * @throws JsonException
     * @throws ReflectionException
     */
    public function testGetIpCheckNull(): void
    {
        $this->setClientIpAddress(ipAddress: null);
        $this->assertNull(
            actual: (new DeviceInfo(ip: DeviceInfo::getIp()))->ip
        );
    }

    /**
     * Expect null returns when we receive a string with multiple ip's.
     *
     * @throws AttributeCombinationException
     * @throws JsonException
     * @throws ReflectionException
     */
    public function testGetIpCheckMultipleCloudy(): void
    {
        $this->setClientIpAddress(ipAddress: $this->strangeCloudflare);
        $this->assertNull(
            actual: (new DeviceInfo(ip: DeviceInfo::getIp()))->ip
        );
    }

    /**
     * @throws AttributeCombinationException
     * @throws JsonException
     * @throws ReflectionException
     */
    public function testGetIpCheckV4default(): void
    {
        $this->setClientIpAddress(ipAddress: $this->ip4normal);
        $this->assertSame(
            expected: $this->ip4normal,
            actual: (new DeviceInfo(ip: DeviceInfo::getIp()))->ip
        );
    }

    /**
     * @throws AttributeCombinationException
     * @throws JsonException
     * @throws ReflectionException
     */
    public function testGetIpCheckV6NetShort(): void
    {
        $this->setClientIpAddress(ipAddress: $this->ipv6short);
        $this->assertSame(
            expected: $this->ipv6short,
            actual: (new DeviceInfo(ip: DeviceInfo::getIp()))->ip
        );
    }

    /**
     * @throws AttributeCombinationException
     * @throws JsonException
     * @throws ReflectionException
     */
    public function testGetIpCheckV6NetLong(): void
    {
        $this->setClientIpAddress(ipAddress: $this->ipv6long);
        $this->assertSame(
            expected: $this->ipv6long,
            actual: (new DeviceInfo(ip: DeviceInfo::getIp()))->ip
        );
    }

    /**
     * @throws AttributeCombinationException
     * @throws JsonException
     * @throws ReflectionException
     */
    public function testGetIpCheckV6Local(): void
    {
        $this->setClientIpAddress(ipAddress: $this->ipv6LocalLink);
        $this->assertSame(
            expected: $this->ipv6LocalLink,
            actual: (new DeviceInfo(ip: DeviceInfo::getIp()))->ip
        );
    }

    /**
     * @throws AttributeCombinationException
     * @throws JsonException
     * @throws ReflectionException
     */
    public function testGetIpCheckV6Invalid(): void
    {
        $this->setClientIpAddress(ipAddress: $this->ipv6Invalid);
        $this->assertNull(
            actual: (new DeviceInfo(ip: DeviceInfo::getIp()))->ip
        );
    }

    /**
     * Decimal ip's are expected to not be converted anywhere, so this should be null.
     *
     * @throws AttributeCombinationException
     * @throws JsonException
     * @throws ReflectionException
     */
    public function testGetIpCheckDecimal(): void
    {
        $this->setClientIpAddress(ipAddress: $this->ipDecimal);
        $this->assertNull(
            actual: (new DeviceInfo(ip: DeviceInfo::getIp()))->ip
        );
    }

    /**
     * Spoof and expect a specific user agent.
     */
    public function testDeviceUserAgent(): void
    {
        $_SERVER['HTTP_USER_AGENT'] = 'WooCommerce for Magento v1.0.0';
        $this->assertSame(
            expected: $_SERVER['HTTP_USER_AGENT'],
            actual: DeviceInfo::getUserAgent()
        );
        // Unset after assertion.
        unset($_SERVER['HTTP_USER_AGENT']);
    }
}

<?php

declare(strict_types=1);

namespace Resursbank\Ecom\Module\SupportInfo\Widget;

use Resursbank\Ecom\Lib\Widget\Widget;

class SupportInfo extends Widget
{
    public function __construct(
        public readonly string $pluginVersion = ''
    ) {

    }

    /**
     * Fetches the current PHP version.
     */
    public static function getPhpVersion(): string
    {
        return PHP_VERSION;
    }

    public static function getEcomVersion(): string
    {
        return '0.0';
    }

    public static function getExternalIp(): string
    {
        return '127.0.0.1';
    }
}
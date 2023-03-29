<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\SupportInfo\Widget;

use Resursbank\Ecom\Lib\Widget\Widget;

class SupportInfo extends Widget
{
    private readonly string $html;

    public function __construct(
        public readonly string $pluginVersion = ''
    ) {
        $this->html = $this->render(file: __DIR__ . '/support-info.phtml');
    }

    public function getHtml(): string
    {
        return $this->html;
    }

    /**
     * Fetches the current PHP version.
     */
    public function getPhpVersion(): string
    {
        return PHP_VERSION;
    }

    public function getEcomVersion(): string
    {
        try {
            $composerJson = file_get_contents(filename: __DIR__ . '/../../../../composer.json');
            $decoded = json_decode(
                json: $composerJson,
                flags: JSON_THROW_ON_ERROR
            );
            return $decoded->version;
        } catch (\Throwable $error) {

        }
    }

    public function getExternalIp(): string
    {
        return '127.0.0.1';
    }
}
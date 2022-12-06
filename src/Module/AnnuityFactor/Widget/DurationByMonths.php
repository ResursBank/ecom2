<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

/** @noinspection PhpMultipleClassDeclarationsInspection */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\AnnuityFactor\Widget;

use Resursbank\Ecom\Exception\FilesystemException;
use Resursbank\Ecom\Lib\Widget\Widget;

/**
 * Generates script intended to fetch duration in months options for PartPayment widget configuration
 */
class DurationByMonths extends Widget
{
    /** @var string  */
    private readonly string $generatedScript;

    /** @var string  */
    public readonly string $separator;

    /**
     * @param string $endpointUrl
     * @throws FilesystemException
     */
    public function __construct(
        public readonly string $endpointUrl
    ) {
        if (str_contains(haystack: $this->endpointUrl, needle: '?')) {
            $this->separator = '&';
        } else {
            $this->separator = '?';
        }
        $this->generatedScript = $this->render(file: __DIR__ . '/DurationByMonths.phtml');
    }

    /**
     * @return string
     */
    public function getScript(): string
    {
        return $this->generatedScript;
    }
}

<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Config;

use JsonException;
use ReflectionException;
use Resursbank\Ecom\Exception\AttributeCombinationException;
use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Model\UserSettings;

/**
 * Defines network settings.
 */
class Network extends Model
{
    /**
     * @throws JsonException
     * @throws ReflectionException
     * @throws AttributeCombinationException
     */
    public function __construct(
        public readonly string $proxy = '',
        public readonly int $proxyType = 0,
        public int $timeout = UserSettings::DEFAULT_API_TIMEOUT,
        public readonly string $userAgent = ''
    ) {
        parent::__construct();
    }

    public function setTimeout(int $timeout): void
    {
        // No sense in setting 0 or negative value.
        if ($timeout > 0) {
            $this->timeout = $timeout;
        }
    }
}

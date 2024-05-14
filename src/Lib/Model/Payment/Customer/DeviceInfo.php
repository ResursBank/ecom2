<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Payment\Customer;

use JsonException;
use ReflectionException;
use Resursbank\Ecom\Exception\AttributeCombinationException;
use Resursbank\Ecom\Lib\Attribute\Validation\StringLength;
use Resursbank\Ecom\Lib\Model\Model;

/**
 * Information and details about a payment.
 */
class DeviceInfo extends Model
{
    /**
     * @throws AttributeCombinationException
     * @throws JsonException
     * @throws ReflectionException
     */
    public function __construct(
        public readonly ?string $ip = null,
        #[StringLength(
            min: 1,
            max: 255
        )] public readonly ?string $userAgent = null
    ) {
        parent::__construct();
    }
}

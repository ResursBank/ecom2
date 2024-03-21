<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Payment\Customer;

use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Attribute\Validation\StringLength;
use Resursbank\Ecom\Lib\Model\Model;

/**
 * Information and details about a payment.
 */
class DeviceInfo extends Model
{
    /**
     * @throws IllegalValueException
     */
    public function __construct(
        /**
         * @todo Don't know how to validate ip-address.
         */
        public readonly ?string $ip = null,
        #[StringLength(
            min: 1,
            max: 200
        )] public readonly ?string $userAgent = null
    ) {
        parent::__construct();
    }
}

<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Payment;

use JsonException;
use ReflectionException;
use Resursbank\Ecom\Exception\AttributeCombinationException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Model\Address;
use Resursbank\Ecom\Lib\Model\CustomerType;
use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Model\Payment\Customer\DeviceInfo;
use Resursbank\Ecom\Lib\Utilities\Strings;

use function is_string;

/**
 * Customer data supplied to create a payment.
 *
 * NOTE: Regarding the $governmentId parameter definition. This data is not
 * required by Resurs Bank to successfully create a payment, if it isn't
 * supplied by us in the request to create the payment Resurs Bank will
 * request the client to enter it on the gateway. However, if we submit an
 * empty value, or NULL, this will cause the API to interpret the data which
 * results in an error. Thus, this value must be unset from this object
 * before we execute the request, and so it cannot be readonly. To summarize
 * nullable because it isn't required, not readonly since an empty string or
 * NULL causes a problem in the API request.
 */
class Customer extends Model
{
    /**
     * @param string|null $governmentId To understand why this is nullable, and not readonly, see the note above.
     * @throws IllegalValueException
     * @throws ReflectionException
     * @throws AttributeCombinationException
     * @throws JsonException
     */
    public function __construct(
        public readonly ?Address $deliveryAddress = null,
        public readonly ?CustomerType $customerType = null,
        public readonly ?string $email = null,
        public ?string $governmentId = null,
        public readonly ?string $mobilePhone = null,
        public readonly ?DeviceInfo $deviceInfo = null
    ) {
        $this->validateEmail();
        parent::__construct();
    }

    /**
     * @throws IllegalValueException
     */
    protected function validateEmail(): void
    {
        if (!is_string(value: $this->email)) {
            return;
        }

        if (!Strings::isEmail(value: $this->email)) {
            throw new IllegalValueException(
                message: 'Email must be a valid email address'
            );
        }
    }
}

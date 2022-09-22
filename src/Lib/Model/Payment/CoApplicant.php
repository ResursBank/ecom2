<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Payment;

use Resursbank\Ecom\Lib\Model\Model;

/**
 * CoApplicant for Customer models in MAPI.
 */
class CoApplicant extends Model
{
    /**
     * @param string $governmentId
     * @param string|null $mobilePhone
     * @param string|null $phone
     * @param string|null $email
     */
    public function __construct(
        /**
         * @todo Not sure how to validate government id.
         */
        public readonly string $governmentId,
        /**
         * @todo Not sure how to validate phone number.
         */
        public readonly ?string $mobilePhone = null,
        /**
         * @todo Not sure how to validate phone number.
         */
        public readonly ?string $phone = null,
        /**
         * @todo Not sure how to validate email.
         */
        public readonly ?string $email = null,
    ) {
    }
}

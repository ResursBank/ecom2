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
     * @param string $mobilePhone
     * @param string $phone
     * @param string $email
     * @param Identification|null $identification
     */
    public function __construct(
        /**
         * @todo Not sure how to validate government id.
         */
        public readonly string $governmentId,
        /**
         * @todo Not sure how to validate phone number.
         */
        public readonly string $mobilePhone,
        /**
         * @todo Not sure how to validate phone number.
         */
        public readonly string $phone,
        /**
         * @todo Not sure how to validate email.
         */
        public readonly string $email,
        public readonly ?Identification $identification = null
    ) {
    }
}

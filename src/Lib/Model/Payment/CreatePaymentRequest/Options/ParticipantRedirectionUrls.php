<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Payment\CreatePaymentRequest\Options;

use Resursbank\Ecom\Lib\Attribute\Validation\StringMatchesUrl;
use Resursbank\Ecom\Lib\Model\Model;

/**
 * Application data for a payment.
 */
class ParticipantRedirectionUrls extends Model
{
    public function __construct(
        #[StringMatchesUrl] public readonly ?string $failUrl,
        #[StringMatchesUrl] public readonly ?string $successUrl
    ) {
        parent::__construct();
    }
}

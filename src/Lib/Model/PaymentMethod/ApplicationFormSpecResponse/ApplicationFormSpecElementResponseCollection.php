<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\PaymentMethod\ApplicationFormSpecResponse;

use Resursbank\Ecom\Lib\Collection\Collection;

/**
 * @SuppressWarnings(PHPMD.LongClassName)
 */
class ApplicationFormSpecElementResponseCollection extends Collection
{
    public function __construct(array $data)
    {
        parent::__construct($data, ApplicationFormSpecElementResponse::class);
    }
}

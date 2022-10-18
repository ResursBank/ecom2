<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\PaymentMethod\ApplicationFormSpecResponse\ApplicationFormSpecElementResponse;

use Resursbank\Ecom\Lib\Collection\Collection;

class ApplicationFormSpecElementOptionResponseCollection extends Collection
{
    public function __construct(array $data)
    {
        parent::__construct($data, ApplicationFormSpecElementOptionResponse::class);
    }
}

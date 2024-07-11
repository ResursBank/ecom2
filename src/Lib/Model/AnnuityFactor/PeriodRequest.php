<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\AnnuityFactor;

use JsonException;
use ReflectionException;
use Resursbank\Ecom\Exception\AttributeCombinationException;
use Resursbank\Ecom\Lib\Attribute\Validation\StringIsUuid;
use Resursbank\Ecom\Lib\Model\Model;

/**
 * Describes a DurationsByMonth request object
 */
class PeriodRequest extends Model
{
    /**
     * @throws JsonException
     * @throws ReflectionException
     * @throws AttributeCombinationException
     */
    public function __construct(
        #[StringIsUuid] public readonly string $paymentMethodId
    ) {
        parent::__construct();
    }
}

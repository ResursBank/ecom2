<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco;

use JsonException;
use ReflectionException;
use Resursbank\Ecom\Exception\AttributeCombinationException;
use Resursbank\Ecom\Lib\Attribute\Validation\StringLength;
use Resursbank\Ecom\Lib\Model\Model;

/**
 * Implementation of InvoiceLabelsDto
 */
class InvoiceLabels extends Model
{
    /**
     * @throws JsonException
     * @throws ReflectionException
     * @throws AttributeCombinationException
     */
    public function __construct(
        #[StringLength(min: 1, max: 20)]
        public readonly ?string $customerId = null,
        #[StringLength(min: 1, max: 20)]
        public readonly ?string $yourReference = null
    ) {
        parent::__construct();
    }
}

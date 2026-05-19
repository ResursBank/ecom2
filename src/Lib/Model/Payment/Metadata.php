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
use Resursbank\Ecom\Lib\Attribute\Validation\StringLength;
use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Model\Payment\Metadata\EntryCollection;

/**
 * Metadata information class for payments. Currently, it does not have a proper collection.
 */
class Metadata extends Model
{
    /**
     * @throws JsonException
     * @throws ReflectionException
     * @throws AttributeCombinationException
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function __construct(
        public readonly ?string $creator = null,
        #[StringLength(
            max: 20
        )] public readonly ?string $externalCustomerId = null,
        #[StringLength(
            max: 46
        )] public readonly ?string $externalInvoiceReference = null,
        public readonly ?EntryCollection $custom = null
    ) {
        parent::__construct();
    }
}

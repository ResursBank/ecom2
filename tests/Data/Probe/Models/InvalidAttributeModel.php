<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Data\Probe\Models;

use Resursbank\Ecom\Lib\Attribute\Validation\CollectionSize;
use Resursbank\Ecom\Lib\Attribute\Validation\IntValue;
use Resursbank\Ecom\Lib\Attribute\Validation\StringIsUrl;
use Resursbank\Ecom\Lib\Model\Model;

class InvalidAttributeModel extends Model
{
    public function __construct(
        #[CollectionSize] #[IntValue(
            min:10
        )] #[StringIsUrl] public readonly string $property
    ) {
        parent::__construct();
    }
}

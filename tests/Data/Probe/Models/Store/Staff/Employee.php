<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Data\Probe\Models\Store\Staff;

use Resursbank\Ecom\Lib\Attribute\Probe\Probable;
use Resursbank\Ecom\Lib\Attribute\Validation\IntValue;
use Resursbank\Ecom\Lib\Attribute\Validation\StringLength;
use Resursbank\EcomTest\Data\Probe\Models\Store\Staff;

/**
 * Test class.
 */
#[Probable]
class Employee extends Staff
{
    public function __construct(
        #[StringLength(min: 100, max: 255)] public readonly string $comment,
        public readonly bool $good,
        public readonly float $test,
        public readonly int $rating,
        public readonly string $title,
        public readonly array $comments,
        #[IntValue(
            min: 1,
            max: 12
        )] public readonly ?int $contractLength = null
    ) {
        parent::__construct(
            rank: 'Normal',
            salary: 100,
            name: 'That one',
            breaks: ['Tuesday', 'Thursday'],
            likes: [
                'Things',
                'Other things',
                'Yoda',
                'Potato',
                'testing bikes'
            ]
        );
    }
}

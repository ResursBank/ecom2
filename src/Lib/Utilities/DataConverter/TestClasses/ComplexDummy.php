<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Utilities\DataConverter\TestClasses;

/**
 * To test stdClass conversion of objects specifying object properties.
 */
class ComplexDummy
{
    /**
     * @param int $int
     * @param SimpleDummy $simpleDummy
     */
    public function __construct(
        public int $int,
        public SimpleDummy $simpleDummy,
        public SimpleDummyCollection $simpleDummyCollection
    ) {
    }
}

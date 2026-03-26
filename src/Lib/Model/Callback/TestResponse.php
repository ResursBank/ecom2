<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Callback;

use JsonException;
use ReflectionException;
use Resursbank\Ecom\Exception\AttributeCombinationException;
use Resursbank\Ecom\Lib\Attribute\Validation\IntValue;
use Resursbank\Ecom\Lib\Model\Callback\Enum\TestStatus;
use Resursbank\Ecom\Lib\Model\Model;

/**
 * Implementation of response from triggering test callback.
 */
class TestResponse extends Model
{
    /**
     * @throws JsonException
     * @throws ReflectionException
     * @throws AttributeCombinationException
     */
    public function __construct(
        public readonly TestStatus $status,
        #[IntValue(min:100, max:599)] public readonly int $code
    ) {
        parent::__construct();
    }
}

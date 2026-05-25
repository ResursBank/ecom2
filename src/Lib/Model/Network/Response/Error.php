<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Network\Response;

use JsonException;
use ReflectionException;
use Resursbank\Ecom\Exception\AttributeCombinationException;
use Resursbank\Ecom\Lib\Attribute\Validation\StringIsDatetime;
use Resursbank\Ecom\Lib\Attribute\Validation\StringNotEmpty;
use Resursbank\Ecom\Lib\Model\Model;

/**
 * Response from some CURL requests contains an error trace.
 */
class Error extends Model
{
    /**
     * @throws JsonException
     * @throws ReflectionException
     * @throws AttributeCombinationException
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        #[StringNotEmpty] public readonly string $traceId,
        #[StringNotEmpty] public readonly string $code,
        #[StringNotEmpty] public readonly string $message,
        #[StringNotEmpty] #[StringIsDatetime] readonly string $timestamp
    ) {
        parent::__construct();
    }
}

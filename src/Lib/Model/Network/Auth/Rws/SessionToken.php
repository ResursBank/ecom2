<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Network\Auth\Rws;

use JsonException;
use ReflectionException;
use Resursbank\Ecom\Exception\AttributeCombinationException;
use Resursbank\Ecom\Lib\Attribute\Validation\StringIsDatetime;
use Resursbank\Ecom\Lib\Attribute\Validation\StringNotEmpty;
use Resursbank\Ecom\Lib\Model\Model;

/**
 * Describes RWS session token.
 *
 * @SuppressWarnings(PHPMD.CamelCasePropertyName)
 * @SuppressWarnings(PHPMD.CamelCaseVariableName)
 * @SuppressWarnings(PHPMD.CamelCaseParameterName)
 */
class SessionToken extends Model
{
    /**
     * @throws AttributeCombinationException
     * @throws JsonException
     * @throws ReflectionException
     */
    public function __construct(
        #[StringNotEmpty] public readonly string $token,
        #[StringIsDatetime] public readonly string $expiresAt
    ) {
        parent::__construct();
    }

    public function isExpired(): bool
    {
        return strtotime($this->expiresAt) <= time();
    }

    public function __toString(): string
    {
        return $this->token;
    }
}

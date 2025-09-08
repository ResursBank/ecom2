<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Network\Auth\Jwt;

use JsonException;
use ReflectionException;
use Resursbank\Ecom\Exception\AttributeCombinationException;
use Resursbank\Ecom\Lib\Attribute\Validation\StringNotEmpty;
use Resursbank\Ecom\Lib\Model\Model;

/**
 * Describes JWT token.
 *
 * @SuppressWarnings(PHPMD.CamelCasePropertyName)
 * @SuppressWarnings(PHPMD.CamelCaseVariableName)
 * @SuppressWarnings(PHPMD.CamelCaseParameterName)
 */
class Token extends Model
{
    /**
     * @param int $expires_in Time to expiration, provided by API response.
     * @param int|null $expires_at Actual expiration time, calculated.
     * @throws AttributeCombinationException
     * @throws JsonException
     * @throws ReflectionException
     * @todo $tokenType should be an enum. See ECP-227
     */
    public function __construct(
        #[StringNotEmpty] public readonly string $access_token,
        #[StringNotEmpty] public readonly string $token_type,
        public readonly int $expires_in = 0,
        public ?int $expires_at = null
    ) {
        parent::__construct();

        if ($this->expires_at !== null) {
            return;
        }

        $this->expires_at = $this->expires_in + time();
    }

    public function isExpired(): bool
    {
        return $this->expires_at <= time();
    }

    public function __toString(): string
    {
        return $this->access_token;
    }
}

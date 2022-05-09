<?php

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Api;

/**
 * Describes a response from an API request.
 */
interface ResponseInterface
{
    /**
     * Validate response from outgoing API request. The intention is to confirm
     * the API responded with the expected data.
     *
     * @return bool
     */
    public function validate(): bool;
}

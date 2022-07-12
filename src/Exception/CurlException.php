<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Exception;

use Exception;
use Throwable;

/**
 * Exceptions thrown from the curl library.
 */
class CurlException extends Exception
{
    protected ?string $requestBody;

    public function __construct(
        string $message = "",
        int $code = 0,
        ?Throwable $previous = null,
        ?string $requestBody = null
    ) {
        $this->requestBody = $requestBody;
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return string|null
     */
    public function getRequestBody(): ?string
    {
        return $this->requestBody;
    }
}

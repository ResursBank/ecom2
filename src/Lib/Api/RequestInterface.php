<?php

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Api;

/**
 * Describes an outgoing API request.
 */
interface RequestInterface
{
    /**
     * Perform request.
     *
     * @return ResponseInterface
     */
    public function execute(): ResponseInterface;

    /**
     * Perform validation. The intention is to confirm the data and conditions
     * required to successfully complete the outgoing request.
     *
     * @return bool
     */
    public function validate(): bool;
}

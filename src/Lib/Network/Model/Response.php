<?php

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Network\Model;

use stdClass;

/**
 * Curl response.
 */
class Response
{
    /**
     * @param stdClass $body
     * @param int $code
     */
    public function __construct(
        public readonly stdClass $body,
        public readonly int $code
    ) {
    }
}
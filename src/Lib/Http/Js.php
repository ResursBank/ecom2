<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Http;

/**
 * This class is the base controller to return JS content.
 *
 * @SuppressWarnings(PHPMD.ShortClassName)
 */
abstract class Js
{
    /**
     * Contract for the extending class to implement.
     */
    abstract public function getContent(): string;

    /**
     * Respond with the JS content.
     */
    public function respond(): void
    {
        // Set the content type to JS.
        header(header: 'Content-Type: application/javascript');

        // Set response code to 200 OK.
        http_response_code(response_code: 200);

        // Output the JS content.
        echo $this->getContent();
    }
}

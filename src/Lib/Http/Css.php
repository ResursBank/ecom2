<?php

/**
* Copyright © Resurs Bank AB. All rights reserved.
* See LICENSE for license details.
*/

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Http;

/**
* This class is the base controller to return CSS content.
*/
abstract class Css
{
    /**
     * Contract for the extending class to implement.
     */
    abstract public function getContent(): string;

    /**
     * Respond with the CSS content.
     */
    public function respond(): void
    {
        // Set the content type to CSS.
        header(header: 'Content-Type: text/css');

        // Set response code to 200 OK.
        http_response_code(response_code: 200);

        // Output the CSS content.
        echo $this->getContent();
    }
}

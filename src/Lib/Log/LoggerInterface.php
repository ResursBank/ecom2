<?php

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Log;

use Exception;

/**
 * Contract for a logger implementation.
 */
interface LoggerInterface
{
    /**
     * @param string|Exception $message
     * @return void
     */
    public function debug(string|Exception $message): void;
    
    /**
     * @param string|Exception $message
     * @return void
     */
    public function info(string|Exception $message): void;

    /**
     * @param string|Exception $message
     * @return void
     */
    public function warning(string|Exception $message): void;

    /**
     * @param string|Exception $message
     * @return void
     */
    public function error(string|Exception $message): void;
}

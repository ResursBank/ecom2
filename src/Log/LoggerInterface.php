<?php

declare(strict_types=1);

namespace Resursbank\Ecom\Log;

use Exception;

/**
 * Contract for a logger implementation.
 */
interface LoggerInterface
{
    /**
     * @param string|Exception $msg
     * @return void
     */
    public function debug(string|Exception $msg): void;
    
    /**
     * @param string|Exception $msg
     * @return void
     */
    public function info(string|Exception $msg): void;

    /**
     * @param string|Exception $msg
     * @return void
     */
    public function warning(string|Exception $msg): void;

    /**
     * @param string|Exception $msg
     * @return void
     */
    public function error(string|Exception $msg): void;
}

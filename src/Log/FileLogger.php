<?php

declare(strict_types=1);

namespace Resursbank\Ecom\Log;

use Exception;

/**
 * Write logfiles to disk.
 */
class FileLogger implements LoggerInterface
{
    /**
     * @param string $path
     */
    public function __construct(
        private readonly string $path
    ) {
        $this->validatePath();
    }

    /**
     * Write log entry to file on disk.
     *
     * @param string $level
     * @param string $msg
     * @return void
     */
    public function write(
        string $level,
        string $msg
    ): void {
        $file = $this->path . '/' . $level . '.log';
        // @todo Validate that $file is writable.
        // @todo Write log message to file.
    }

    public function info(string $msg): void
    {
        $this->write('info', $msg);
    }

    public function error(string $msg): void
    {
        $this->write('error', $msg);
    }

    public function warning(string $msg): void
    {
        $this->write('warning', $msg);
    }

    public function exception(Exception $e): void
    {
        // @todo Refine this.
        $this->write('exception', $e->getTraceAsString());
    }

    /**
     * Validate logfile storage path.
     */
    private function validatePath(): void
    {
        // @todo Validate that $this->>path is not empty.
        // @todo If $this->>path does not exists, attempt to create it?
        // @todo Validate that $this->>path exists and is writable.
    }
}

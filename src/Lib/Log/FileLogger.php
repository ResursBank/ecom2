<?php

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Log;

use DateTime;
use Exception;
use Resursbank\Ecom\Exception\EmptyException;
use Resursbank\Ecom\Exception\FilesystemException;
use Resursbank\Ecom\Exception\FormatException;
use Resursbank\Ecom\Exception\ValidationException;

/**
 * Write logfiles to disk.
 */
class FileLogger implements LoggerInterface
{
    private const LOG_FILENAME = 'ecom.log';
    private const PATH_ERR_EMPTY = 'Specified log file path is empty';
    private const PATH_ERR_WHITESPACE = 'Specified log file path has trailing or leading whitespace';
    private const PATH_ERR_TRAILING_SEPARATOR = 'Specified log file path has a trailing directory separator character';
    private const PATH_ERR_FILE_DOES_NOT_EXIST = 'Specified log file path does not exist';
    private const PATH_ERR_FILE_NOT_DIRECTORY = 'Specified log file path is not a directory';
    private const PATH_ERR_FILE_NOT_WRITABLE = 'Specified log file path is not writable';
    private const WRITE_ERROR = 'No data was written to the log file';
    private const ERR_UNWRITABLE = 'Log file appears to be unwritable';

    /**
     * @param string $path
     * @throws EmptyException
     * @throws ValidationException
     */
    public function __construct(
        private readonly string $path
    ) {
        $this->validatePath();
    }

    /**
     * Logs message with log level DEBUG
     *
     * @param string|Exception $message
     * @return void
     * @throws FilesystemException
     */
    public function debug(string|Exception  $message): void
    {
        $this->log(level: LogLevel::DEBUG, message: $message);
    }

    /**
     * Logs message with log level INFO
     *
     * @param string|Exception $message
     * @return void
     * @throws FilesystemException
     */
    public function info(string|Exception $message): void
    {
        $this->log(level: LogLevel::INFO, message: $message);
    }

    /**
     * Logs message with log level WARNING
     *
     * @param string|Exception $message
     * @return void
     * @throws FilesystemException
     */
    public function warning(string|Exception $message): void
    {
        $this->log(level: LogLevel::WARNING, message: $message);
    }

    /**
     * Logs message with log level ERROR
     *
     * @param string|Exception $message
     * @return void
     * @throws FilesystemException
     */
    public function error(string|Exception $message): void
    {
        $this->log(level: LogLevel::ERROR, message: $message);
    }

    /**
     * Write log entry to file on disk.
     *
     * @param LogLevel $level
     * @param string|Exception $message
     * @return void
     * @throws FilesystemException
     */
    private function log(LogLevel $level, string|Exception $message): void
    {
        /**
         * @psalm-suppress RedundantCondition
         */
        if (is_object(value: $message) &&
            (
                get_class(object: $message) === Exception::class ||
                is_subclass_of(object_or_class: $message, class: Exception::class) // @phpstan-ignore-line
            )
        ) {
            $this->logException(e: $message);
        } else {
            $timestamp = new DateTime();
            $formattedMessage = $timestamp->format(format: 'c') . ' ' . $level->name . ': '. $message;

            if ($this->logIsWritable()) {
                if (!file_put_contents(
                    filename: $this->getFilename(),
                    data: $formattedMessage . PHP_EOL,
                    flags: FILE_APPEND | LOCK_EX
                )) {
                    throw new FilesystemException(message: self::WRITE_ERROR);
                }
            } else {
                throw new FilesystemException(message: self::ERR_UNWRITABLE);
            }
        }
    }

    /**
     * Log Exception object by converting it to a string and feeding it to the log method
     *
     * @param Exception $e
     * @return void
     * @throws FilesystemException
     */
    private function logException(Exception $e): void
    {
        $this->log(level: LogLevel::EXCEPTION, message: $e->getTraceAsString());
    }

    /**
     * Returns absolute path to log file
     *
     * @return string
     */
    private function getFilename(): string
    {
        return $this->path . DIRECTORY_SEPARATOR . self::LOG_FILENAME;
    }

    /**
     * Validate logfile storage path.
     *
     * @throws EmptyException
     * @throws ValidationException
     * @return bool
     */
    private function validatePath(): bool
    {
        if ($this->path === '') {
            throw new EmptyException(message: self::PATH_ERR_EMPTY);
        } elseif ($this->path !== trim(string: $this->path)) {
            throw new FormatException(message: self::PATH_ERR_WHITESPACE);
        } elseif (DIRECTORY_SEPARATOR === substr(string: $this->path, offset: -1)) {
            throw new FormatException(message: self::PATH_ERR_TRAILING_SEPARATOR);
        } elseif (!file_exists(filename: $this->path)) {
            throw new FilesystemException(message: self::PATH_ERR_FILE_DOES_NOT_EXIST);
        } elseif (!is_dir(filename: $this->path)) {
            throw new FilesystemException(message: self::PATH_ERR_FILE_NOT_DIRECTORY);
        } elseif (!is_writable(filename: $this->path)) {
            throw new FilesystemException(message: self::PATH_ERR_FILE_NOT_WRITABLE);
        }

        return true;
    }

    /**
     * Checks if the log file is writable
     *
     * @return bool
     */
    private function logIsWritable(): bool
    {
        // Consider file writable if it either exists, isn't a directory and is writable or it doesn't exist but the
        // parent directory passes the validation test
        try {
            if ((file_exists(filename: $this->getFilename()) && is_writable(filename: $this->getFilename())) ||
                (!file_exists(filename: $this->getFilename()) && $this->validatePath())
            ) {
                return true;
            }
        } catch (Exception $e) {
            return false;
        }

        return false;
    }
}

<?php

declare(strict_types=1);

namespace Resursbank\EcomTest\Lib\Log;

use Exception;
use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Exception\EmptyException;
use Resursbank\Ecom\Exception\FilesystemException;
use Resursbank\Ecom\Exception\FormatException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Log\FileLogger;
use Resursbank\Ecom\Lib\Log\LogLevel;

/**
 * Verifies that the FileLogger class works as intended
 */
final class FileLoggerTest extends TestCase
{
    private const BASE_PATH = '/tmp';
    private const PATH_PREFIX = 'phpunit_FileLoggerTest';
    private const LOG_FILENAME = 'ecom.log';
    private string $path;
    private string $filename;
    private FileLogger $logger;
    private string $message;

    /**
     * Set up our required variables, directories and files
     *
     * @return void
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->message = 'This is a test message';

        if (!is_writable(filename: self::BASE_PATH)) {
            $this->markTestSkipped(message: self::BASE_PATH.' directory is not writable, skipping test');
        }

        $this->path = self::BASE_PATH . DIRECTORY_SEPARATOR . self::PATH_PREFIX . '_' .
            bin2hex(string: random_bytes(length: 8));
        $this->filename = $this->path . DIRECTORY_SEPARATOR . self::LOG_FILENAME;

        if (!mkdir(directory: $this->path)) {
            $this->markTestSkipped(message: 'Failed to create test directory');
        }
        
        if (!touch(filename: $this->filename)) {
            $this->markTestSkipped(message: 'Failed to touch log file');
        }

        $this->logger = new FileLogger(path: $this->path);
    }

    /**
     * Clean up the files and directories we created
     *
     * @return void
     */
    protected function tearDown(): void
    {
        /*if (file_exists(filename: $this->filename) && !unlink(filename: $this->filename)) {
            $this->markTestSkipped(message: 'Failed to delete the test log file');
        }
        if (!rmdir(directory: $this->path)) {
            $this->markTestSkipped(message: 'Failed to delete test directory');
        }*/
    }

    /**
     * Fetches the last line logged to specified file
     *
     * @param string $filename
     * @return string
     */
    private function getLastLineFromFile(string $filename): string
    {
        $lines = file(filename: $filename);
        return $lines[count($lines)-1];
    }

    /**
     * Verify that a FilesystemException is thrown if we attempt to write to an unwritable file.
     *
     * @return void
     * @throws FilesystemException
     */
    public function testLoggingFailure(): void
    {
        if (!chmod(filename: $this->filename, permissions: 0000)) {
            $this->markTestSkipped('Failed to set file permissions');
        }

        $className = false;
        try {
            $this->logger->debug(message: $this->message);
        } catch (Exception $e) {
            $className = get_class(object: $e);
        }

        $this->assertSame(expected: FilesystemException::class, actual: $className);
    }

    /**
     * Verify that debug logging works
     *
     * @return void
     * @throws FilesystemException
     */
    public function testLogDebug(): void
    {
        $this->logger->debug(message: $this->message);
        $loggedDebug = substr(
            string: $this->getLastLineFromFile(filename: $this->filename),
            offset: 26
        );
        $this->assertEquals(expected: LogLevel::DEBUG->name . ': ' . $this->message . PHP_EOL, actual: $loggedDebug);
    }

    /**
     * Verify that info logging works
     *
     * @return void
     * @throws FilesystemException
     */
    public function testLogInfo(): void
    {
        $this->logger->info(message: $this->message);
        $loggedInfo = substr(
            string: $this->getLastLineFromFile(filename: $this->filename),
            offset: 26
        );
        $this->assertEquals(expected: LogLevel::INFO->name . ': ' . $this->message . PHP_EOL, actual: $loggedInfo);
    }

    /**
     * Verify that warning logging works
     *
     * @return void
     * @throws FilesystemException
     */
    public function testLogWarning(): void
    {
        $this->logger->warning(message: $this->message);
        $loggedWarning = substr(
            string: $this->getLastLineFromFile(filename: $this->filename),
            offset: 26
        );
        $this->assertEquals(
            expected: LogLevel::WARNING->name . ': ' . $this->message . PHP_EOL,
            actual: $loggedWarning
        );
    }

    /**
     * Verify that error logging works
     *
     * @return void
     * @throws FilesystemException
     */
    public function testLogError(): void
    {
        $this->logger->error(message: $this->message);
        $loggedError = substr(
            string: $this->getLastLineFromFile(filename: $this->filename),
            offset: 26
        );
        $this->assertEquals(expected: LogLevel::ERROR->name.': '.$this->message . PHP_EOL, actual: $loggedError);
    }

    /**
     * Verify that Exceptions get logged
     *
     * @return void
     */
    public function testLogException(): void
    {
        $e = new Exception();
        $this->logger->debug($e);
        $numLines = count(value: file(filename: $this->filename));
        $lastLine = $this->getLastLineFromFile(filename: $this->filename);
        $expectedLastLine = '#' . ($numLines-1) . ' {main}' . PHP_EOL;
        $this->assertEquals(expected: $expectedLastLine, actual: $lastLine);
    }

    /**
     * Verify that creating a FileLogger with an empty path throws an EmptyException
     *
     * @return void
     */
    public function testValidatePathWithEmptyPath(): void
    {
        //$this->expectException(exception: EmptyException::class);
        $className = false;
        try {
            new FileLogger(path: '');
        } catch (Exception $e) {
            $className = get_class(object: $e);
        }

        $this->assertSame(expected: EmptyException::class, actual: $className);
    }

    /**
     * Verify that creating a FileLogger with a path with leading whitespace throws a ValidationException
     *
     * @return void
     */
    public function testValidatePathWithLeadingWhitespace(): void
    {
        $className = false;
        try {
            new FileLogger(path: '/tmp ');
        } catch (Exception $e) {
            $className = get_class(object: $e);
        }

        $this->assertSame(expected: FormatException::class, actual: $className);
    }

    /**
     * Verify that creating a FileLogger with a path with trailing whitespace throws a ValidationException
     *
     * @return void
     */
    public function testValidatePathWithTrailingWhitespace(): void
    {
        $className = false;
        try {
            new FileLogger(path: ' /tmp');
        } catch (Exception $e) {
            $className = get_class(object: $e);
        }

        $this->assertSame(expected: FormatException::class, actual: $className);
    }

    /**
     * Verify that creating a FileLogger with a path with a trailing directory separator character
     * throws a ValidationException
     *
     * @return void
     */
    public function testValidatePathWithTrailingSeparator(): void
    {
        $className = false;
        try {
            new FileLogger(path: '/tmp/');
        } catch (Exception $e) {
            $className = get_class(object: $e);
        }

        $this->assertSame(expected: FormatException::class, actual: $className);
    }

    /**
     * Verify that attempting to create a FileLogger using a non-existent path causes a ValidationException
     *
     * @return void
     * @throws Exception
     */
    public function testValidatePathWhichDoesNotExist(): void
    {
        $fakePath = $this->path . bin2hex(string: random_bytes(length: 8));

        if (file_exists($fakePath)) {
            $this->markTestSkipped(message: "Path exists when it shouldn't, skipping");
        }

        $className = false;
        try {
            new FileLogger(path: $fakePath);
        } catch (Exception $e) {
            $className = get_class(object: $e);
        }

        $this->assertSame(expected: FilesystemException::class, actual: $className);
    }

    /**
     * Verify that creating a FileLogger using a path which is not a directory causes a ValidationException
     *
     * @return void
     */
    public function testValidatePathWhichIsNotDirectory(): void
    {
        $filePath = $this->path . DIRECTORY_SEPARATOR . bin2hex(string: random_bytes(length: 8));
        if (!touch(filename: $filePath)) {
            $this->markTestSkipped(message: 'Failed to create file for test');
        }

        $className = false;
        try {
            new FileLogger(path: $filePath);
        } catch (Exception $e) {
            $className = get_class(object: $e);
        }

        unlink($filePath);

        $this->assertSame(expected: FilesystemException::class, actual: $className);
    }

    /**
     * Verify that creating a FileLogger using a path which is unwritable causes a ValidationException
     *
     * @return void
     */
    public function testValidatePathWhichIsUnwritable(): void
    {
        if (!chmod(filename: $this->path, permissions: 0400)) {
            $this->markTestSkipped(message: 'Failed to change path directory permissions');
        }

        $className = false;
        try {
            new FileLogger($this->path);
        } catch (Exception $e) {
            $className = get_class(object: $e);
        }

        if (!chmod(filename: $this->path, permissions: 0755)) {
            $this->markTestSkipped(message: 'Failed to change path directory permissions');
        }

        $this->assertSame(expected: FilesystemException::class, actual: $className);
    }

    /**
     * Verify that creating a FileLogger using a valid path raises no exceptions
     *
     * @return void
     */
    public function testValidatePathWithNoErrors(): void
    {
        $logger = false;
        try {
            $logger = new FileLogger(path: $this->path);
        } catch (Exception $e) {
            $this->fail(message: 'Exception thrown with valid path');
        }

        $this->assertSame(expected: FileLogger::class, actual: get_class(object: $logger));
    }
}

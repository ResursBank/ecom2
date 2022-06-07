<?php

declare(strict_types=1);

namespace Resursbank\EcomTest\Lib\Cache;

use Exception;
use JsonException;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use Resursbank\Ecom\Exception\FilesystemException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Cache\Filesystem;
use Resursbank\Ecom\Lib\Cache\Model\Data;

/**
 * This class will test Filesystem cache methods.
 */
class FilesystemTest extends TestCase
{
    /**
     * Base path of the directories and files these tests will create.
     */
    private const BASE_PATH = '/tmp/resursbank-test';

    /**
     * @return void
     */
    protected function setUp(): void
    {
        // Create directory where all other directories / files will be created
        // during our tests, to avoid bloating /tmp.
        if (!is_dir(self::BASE_PATH)) {
            mkdir(self::BASE_PATH, 0755, true);
        }
        
        parent::setUp();
    }
    
    /**
     * Create new Filesystem instance.
     *
     * @param string $path
     * @return Filesystem
     */
    private function getFilesystem(string $path): Filesystem
    {
        return new Filesystem($path);
    }

    /**
     * Generate unique path name, to ensure various tests which create files and
     * directories won't interfere with each other.
     *
     * @return string
     * @throws Exception
     */
    private function getPath(): string
    {
        return (
            self::BASE_PATH .
            '/ecom-' .
            random_int(0, 99999) .
            time() .
            random_int(0, 99999)
        );
    }

    /**
     * Retrieve accessible createPath() method.
     *
     * @param Filesystem $fs
     * @return ReflectionMethod
     */
    private function getCreatePathMethod(Filesystem $fs): ReflectionMethod
    {
        $class = new ReflectionClass($fs);
        $method = $class->getMethod('createPath');
        $method->setAccessible(true);

        return $method;
    }

    /**
     * Retrieve accessible getFile() method.
     *
     * @param Filesystem $fs
     * @return ReflectionMethod
     */
    private function getGetFileMethod(Filesystem $fs): ReflectionMethod
    {
        $class = new ReflectionClass($fs);
        $method = $class->getMethod('getFile');
        $method->setAccessible(true);

        return $method;
    }

    /**
     * This test will assert three things:
     *
     * 1. The cache directory does not exist.
     * 2. The cache directory is created when we execute createPath().
     * 3. The directory which was created is writable by the PHP process.
     *
     * NOTE: This method will then remove the lowest level directory it creates
     * to ensure subsequent tests will function properly.
     *
     * @return void
     * @throws ReflectionException
     * @throws Exception
     */
    public function testCreatePathCreatesWritableDir(): void
    {
        $path = $this->getPath();
        $fs = $this->getFilesystem($path);
        $method = $this->getCreatePathMethod($fs);

        self::assertDirectoryDoesNotExist($path);

        $method->invoke($fs);

        self::assertDirectoryExists($path);
        self::assertDirectoryIsWritable($path);

        rmdir($path);
    }

    /**
     * This method will assert two things:
     *
     * 1. If the cache dir path is allocated by a file we get a CacheException.
     * 2. The CacheException specifies a file was allocating our location.
     *
     * NOTE: This method will unlink the file it creates to ensure subsequent
     * test runs will function as expected.
     *
     * @return void
     * @throws ReflectionException
     * @throws Exception
     */
    public function testCreatePathThrowsOnExistingFile(): void
    {
        $path = $this->getPath();
        $fs = $this->getFilesystem($path);
        $method = $this->getCreatePathMethod($fs);

        touch($path);

        $this->expectException(FilesystemException::class);
        $this->expectExceptionMessage($path . ' is a file.');

        $method->invoke($fs);

        unlink($path);
    }

    /**
     * This method asserts two things:
     *
     * 1. If the cache dir exists, but isn't writable we get a CacheException.
     * 2. The CacheException specifies the directory is not writable.
     *
     * @return void
     * @throws ReflectionException
     * @throws Exception
     */
    public function testCreatePathThrowsWithExistingUnwritable(): void
    {
        $path = $this->getPath();
        $fs = $this->getFilesystem($path);
        $method = $this->getCreatePathMethod($fs);

        mkdir($path, 0500, true);

        $this->expectException(FilesystemException::class);
        $this->expectExceptionMessage($path . ' is not writable.');

        $method->invoke($fs);
    }

    /**
     * This method asserts that no Exception is thrown when the specified path
     * is allocated by a writable directory.
     *
     * @return void
     * @throws ReflectionException
     * @throws Exception
     */
    public function testCreatePathWontThrowWithExistingWritable(): void
    {
        $path = $this->getPath();
        $fs = $this->getFilesystem($path);
        $method = $this->getCreatePathMethod($fs);

        mkdir($path, 0755, true);

        $this->expectNotToPerformAssertions();

        $method->invoke($fs);
    }

    /**
     * Assert the getFile() method will generate the expected filepath value.
     *
     * @return void
     * @throws ReflectionException
     */
    public function testKeyToFilePathConversion(): void
    {
        $fs = $this->getFilesystem(self::BASE_PATH);
        $method = $this->getGetFileMethod($fs);

        self::assertSame(
            self::BASE_PATH . '/woho',
            $method->invoke($fs, 'woho')
        );
    }

    /**
     * Assert that method write() throws instance of ValidationException if our
     * key contains illegal characters.
     *
     * @return void
     * @throws Exception
     */
    public function testWriteThrowsWithIllegalKeyCharacter(): void
    {
        $path = $this->getPath();
        $fs = $this->getFilesystem($path);

        $this->expectException(ValidationException::class);
        $fs->write('YAd4!', 'Midgar', 777);
    }

    /**
     * Assert that method write() creates the cache directory.
     *
     * @return void
     * @throws FilesystemException
     * @throws ValidationException
     * @throws Exception
     */
    public function testWriteCreatesDirectory(): void
    {
        $path = $this->getPath();
        $fs = $this->getFilesystem($path);
        $key = 'test' . random_int(0, 999999);

        self::assertDirectoryDoesNotExist($path);

        $fs->write($key, 'Wutai? Shinra, materia#', 0);

        self::assertDirectoryExists($path);
    }

    /**
     * Assert that when we call the method write() it will generate a cache file
     * if none already exist.
     *
     * @return void
     * @throws Exception
     */
    public function testWriteCreatesFile(): void
    {
        $path = $this->getPath();
        $fs = $this->getFilesystem($path);
        $key = 'test' . random_int(0, 999999);
        $file = "$path/$key";

        self::assertFileDoesNotExist($file);

        $fs->write($key, 'nada', 0);

        self::assertFileExists($file);
    }

    /**
     * Assert that the method write() will accept an existing file (meaning it
     * will not attempt to create a file if the file already exists).
     *
     * @return void
     * @throws Exception
     */
    public function testWriteAcceptsExistingFile(): void
    {
        $path = $this->getPath();
        $fs = $this->getFilesystem($path);
        $key = 'test' . random_int(0, 999999);
        $file = "$path/$key";

        mkdir($path, 0755);
        touch($file);

        self::assertFileExists($file);

        $fs->write($key, 'some data', 99);

        self::assertFileExists($file);
    }

    /**
     * Assert that the method write() will throw an instance of
     * FilesystemException with the message "$file is not writable." if the
     * existing cache file isn't writable.
     *
     * @return void
     * @throws Exception
     */
    public function testWriteThrowsIfCacheFileIsUnwritable(): void
    {
        $path = $this->getPath();
        $fs = $this->getFilesystem($path);
        $key = 'test' . random_int(0, 999999);
        $file = "$path/$key";

        mkdir($path, 0755);
        touch($file);
        chmod($file, 0500);

        self::assertFileExists($file);
        self::assertFileIsNotWritable($file);
        $this->expectException(FilesystemException::class);
        $this->expectExceptionMessage("$file is not writable.");

        $fs->write($key, 'Calm fort condor in de sun ~', 1233);
    }

    /**
     * Assert that the method write() will throw an instance of
     * FilesystemException with the message "$file is not a file." if a
     * directory allocates the cache file location.
     *
     * @return void
     * @throws FilesystemException
     * @throws ValidationException
     * @throws JsonException
     * @throws Exception
     */
    public function testWriteThrowsWithExistingDirectory(): void
    {
        $path = $this->getPath();
        $fs = $this->getFilesystem($path);
        $key = 'test' . random_int(0, 999999);
        $file = "$path/$key";

        mkdir($path, 0700);
        mkdir($file, 0500);

        self::assertDirectoryExists($file);
        self::assertDirectoryIsNotWritable($file);
        $this->expectException(FilesystemException::class);
        $this->expectExceptionMessage("$file is not a file.");

        $fs->write(
            $key,
            json_encode(['Junon', 4, '{bb}'], JSON_THROW_ON_ERROR),
            971367
        );
    }

    /**
     * Assert that the method write() creates a file with contents.
     *
     * @return void
     * @throws FilesystemException
     * @throws ValidationException
     * @throws Exception
     */
    public function testWriteCreatesNoneEmptyFile(): void
    {
        $path = $this->getPath();
        $fs = $this->getFilesystem($path);
        $key = 'test' . random_int(0, 999999);
        $file = "$path/$key";

        $fs->write($key, 'Empty', 55);

        self::assertFileExists($file);
        self::assertNotEmpty(file_get_contents($file));
    }

    /**
     * Assert that the method write() inserts a serialized Data object into the
     * cache file.
     *
     * @return void
     * @throws FilesystemException
     * @throws ValidationException
     * @throws Exception
     */
    public function testWriteUsesDataModel(): void
    {
        $path = $this->getPath();
        $fs = $this->getFilesystem($path);
        $key = 'test' . random_int(0, 999999);
        $file = "$path/$key";

        $fs->write($key, 'Bender', 5443);

        $data = unserialize(file_get_contents($file));

        self::assertInstanceOf(Data::class, $data);
    }
}
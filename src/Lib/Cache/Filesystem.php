<?php

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Cache;

use Resursbank\Ecom\Exception\CacheException;
use Resursbank\Ecom\Exception\FilesystemException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Cache\Model\Data;

/**
 * Implements business logic to support filesystem based caching.
 *
 * @todo If we add a health report as discussed we should add some writ- / readable information about the cache dir / files since read will fail silently.
 */
class Filesystem extends AbstractCache implements CacheInterface
{
    /**
     * @param string $path
     */
    public function __construct(
        private readonly string $path
    ) { }

    /**
     * If there should be any problem with the requested cache file, for example
     * if the file exists but isn't writable, its path is allocated by a
     * directory, its content is invalid or corrupt etc. this method will simply
     * return null, meaning it will fail silently.
     *
     * @inheritdoc
     * @throws ValidationException
     * @todo Consider adding logs.
     */
    public function read(string $key): ?Data
    {
        $result = null;

        // Make sure the key consists of valid characters.
        $this->validateKey($key);

        // Read cache file.
        $file = $this->getFile($key);

        if (file_exists($file) && is_file($file) && is_readable($file)) {
            /* NOTE: The silencer is required here because there is no safer way
            to ensure the content of the cache file was in fact a serialized
            Data object. */
            /** @noinspection PhpUsageOfSilenceOperatorInspection */
            $content = @unserialize(
                file_get_contents($file),
                ['allowed_classes' => [Data::class]]
            );

            if (($content instanceof Data) && $content->data !== '') {
                $result = $content;
            }
        }

        return $result;
    }

    /**
     * @inheritdoc
     * @throws ValidationException
     * @throws FilesystemException
     * @todo Consider adding logs.
     */
    public function write(string $key, string $data, int $ttl): void
    {
        // Make sure the key consists of valid characters.
        $this->validateKey($key);

        // Create the cache directory, if it's missing.
        $this->createPath();

        // Create the cache file.
        $filename = $this->getFile($key);

        if (file_exists($filename)) {
            if (!is_file($filename)) {
                throw new FilesystemException("$filename is not a file.");
            }

            if (!is_writable($filename)) {
                throw new FilesystemException("$filename is not writable.");
            }
        }

        file_put_contents($filename, serialize(new Data($data, time() + $ttl)));
    }

    /**
     * @inheritdoc
     * @todo Consider adding logs.
     */
    public function clear(string $key): void
    {
        $this->write($key, '', 0);
    }

    /**
     * Prepare directory where cache is stored by creating it if it doesn't
     * already exist and making sure it's writable.
     *
     * @return void
     * @throws FilesystemException
     */
    private function createPath(): void
    {
        if (file_exists($this->path) && is_file($this->path)) {
            throw new FilesystemException($this->path . ' is a file.');
        }

        if (
            !file_exists($this->path) &&
            !mkdir($this->path, 0755, true) &&
            !is_dir($this->path)
        ) {
            throw new FilesystemException(
                'Failed to create cache dir ' . $this->path
            );
        }

        if (!is_writable($this->path)) {
            throw new FilesystemException($this->path . ' is not writable.');
        }
    }

    /**
     * Convert key to cache file path.
     *
     * @param string $key
     * @return string
     */
    private function getFile(string $key): string
    {
        return "$this->path/$key";
    }
}

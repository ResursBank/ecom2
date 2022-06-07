<?php

declare(strict_types=1);

namespace Resursbank\EcomTest\Lib\Cache;

use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Lib\Cache\Filesystem;

class NoneTest extends TestCase
{
    private Filesystem $filesystem;

    /**
     * Setup filesystem cache instance.
     *
     * @return void
     */
    protected function setup(): void
    {
        $this->filesystem = new Filesystem('/tmp/resursbank/ecom/cache');
    }

    /**
     * Assert that read() method will always return null.
     *
     * @return void
     */
    public function testReadReturnsNull(): void
    {
        self::assertNull($this->filesystem->read('whatever'));
    }

    /**
     * Assert that write() method is callable but doesn't do anything.
     *
     * @return void
     */
    public function testWriteDoesNothing(): void
    {
        $this->filesystem->write('thatkey', 'anything', 9999);
        $this->expectNotToPerformAssertions();
    }

    /**
     * Assert that clear() method is callable but doesn't do anything.
     *
     * @return void
     */
    public function testClearDoesNothing(): void
    {
        $this->filesystem->clear('somekey');
        $this->expectNotToPerformAssertions();
    }
}
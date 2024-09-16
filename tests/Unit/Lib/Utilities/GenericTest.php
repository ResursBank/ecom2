<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Lib\Utilities;

use Exception;
use JsonException;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Resursbank\Ecom\Exception\FilesystemException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Utilities\Generic;

/**
 * Test for Generic class.
 */
class GenericTest extends TestCase
{
    /**
     * @throws ReflectionException
     * @throws IllegalValueException
     */
    public function testGetVersionByDocBlock(): void
    {
        $this->assertTrue(
            condition: version_compare(
                version1: (new Generic())->getVersionByClassDoc(
                    className: Generic::class
                ),
                version2: '1.0.0',
                operator: '>='
            )
        );
    }

    /**
     * @throws Exception
     */
    public function testGetVersionByComposer(): void
    {
        $generic = $this->createMock(originalClassName: Generic::class);
        $generic->method('getVersionByComposer')->willReturn(value: '1.0.0');
        // composer.json in our package may not contain version numbers.
        $this->assertTrue(
            condition: version_compare(
                version1: $generic->getVersionByComposer(location: __DIR__),
                version2: '1.0.0',
                operator: '>='
            )
        );
    }

    /**
     * @throws ReflectionException
     */
    public function testGetVersionByAnythingFound(): void
    {
        $generic = $this->createMock(originalClassName: Generic::class);
        $generic->method('getVersionByAny')->willReturn(value: '1.0.0');
        $this->assertTrue(
            condition: version_compare(
                version1: $generic->getVersionByAny(
                    composerLocation: __DIR__,
                    composerDepth: 3,
                    className: Generic::class
                ),
                version2: '1.0.0',
                operator: '>='
            )
        );
    }

    /**
     * @throws Exception
     */
    public function testGetAnotherComposerTag(): void
    {
        $willReturn = 'resursbank/ecom';

        $this->assertSame(
            expected: $willReturn,
            actual: (new Generic())->getComposerTag(
                location: __DIR__,
                tag: 'name'
            )
        );
    }

    /**
     * @throws Exception
     */
    public function testGetVendor(): void
    {
        $this->assertSame(
            expected: 'resursbank',
            actual: (new Generic())->getComposerVendor(
                composerLocation: __DIR__
            )
        );
    }

    /**
     * Test for getComposerConfig method.
     *
     * @throws Exception
     */
    public function testGetComposerConfig(): void
    {
        $generic = new Generic();

        // Test with a valid composer.json location.
        $validLocation = __DIR__;
        $this->assertNotEmpty(
            actual: $generic->getComposerConfig(location: $validLocation),
            message: 'The composer config should be found at a valid location.'
        );

        // Test with an invalid location (no composer.json).
        $this->expectException(exception: FilesystemException::class);
        $invalidLocation = '/invalid/location';
        $generic->getComposerConfig(location: $invalidLocation);
    }

    /**
     * @throws FilesystemException
     * @throws IllegalValueException
     * @throws JsonException
     */
    public function testGetComposerConfigTriggersOpenBaseDirException(): void
    {
        $generic = new Generic();

        // Simulate a scenario where getComposerConfig receives a location that triggers open_basedir restriction.
        // Set an invalid path to simulate open_basedir issues.
        $invalidLocation = '/path/that/does/not/exist';

        // Expect a FilesystemException when open_basedir restriction is active.
        $this->expectException(exception: FilesystemException::class);

        // Call getComposerConfig with a location that should trigger an open_basedir error.
        $generic->getComposerConfig(location: $invalidLocation);
    }
}

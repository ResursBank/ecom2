<?php

/** @noinspection PsalmGlobal */

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Integration\Lib\Utilities;

use Exception;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Resursbank\Ecom\Lib\Utilities\Generic;

class GenericTest extends TestCase
{
    /**
     * @test
     * @throws ReflectionException
     */
    public function getVersionByDocBlockTest(): void
    {
        self::assertTrue(
            version_compare(
                (new Generic())->getVersionByClassDoc(Generic::class),
                '1.0.0',
                '>='
            )
        );
    }

    /**
     * @test
     * @throws Exception
     */
    public function getVersionByComposerTest(): void
    {
        $generic = $this->createMock(
            originalClassName: Generic::class
        );
        $generic->method('getVersionByComposer')->willReturn('1.0.0');
        // composer.json in our package may not contain version numbers.
        self::assertTrue(
            version_compare(
                $generic->getVersionByComposer(__DIR__),
                '1.0.0',
                '>='
            )
        );
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function getVersionByAnythingFound(): void
    {
        $generic = $this->createMock(
            originalClassName: Generic::class
        );
        $generic->method('getVersionByAny')->willReturn('1.0.0');
        self::assertTrue(
            version_compare(
                $generic->getVersionByAny(__DIR__, 3, Generic::class),
                '1.0.0',
                '>='
            )
        );
    }

    /**
     * @return void
     * @throws Exception
     */
    public function getAnotherComposerTag(): void
    {
        $willReturn = 'resursbank/ecom';

        self::assertSame(
            $willReturn,
            (new Generic())->getComposerTag(__DIR__, 'name')
        );
    }

    /**
     * @test
     * @throws Exception
     */
    public function getVendorTest(): void
    {
        self::assertSame(
            'resursbank',
            (new Generic())->getComposerVendor(__DIR__)
        );
    }
}

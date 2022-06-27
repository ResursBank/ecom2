<?php

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
    public function getVersionByDocBlockTest()
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
    public function getVersionByComposerTest()
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
    public function getVersionByAnythingFound()
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
     * @test
     */
    public function getAnotherComposerTag()
    {
        $willReturn = 'resursbank/ecom';

        self::assertSame(
            $willReturn,
            (new Generic())->getComposerTag(__DIR__, 'name')
        );
    }
}

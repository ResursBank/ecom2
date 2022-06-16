<?php

declare(strict_types=1);

namespace Resursbank\EcomTest\Lib\Utilities;

use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Lib\Utilities\DataConverter;
use Resursbank\Ecom\Lib\Utilities\DataConverter\TestClasses;
use stdClass;

final class DataConverterTest extends TestCase
{
    public function testSimpleConversion(): void
    {
        $data = new stdClass();
        $data->int = 42;
        $data->message = "Foobar";

        $expected = new TestClasses\SimpleDummy(
            int: 42,
            message: "Foobar"
        );

        $output = DataConverter::stdClassToType(object: $data, type: TestClasses\SimpleDummy::class);

        $this::assertEquals(
            expected: $expected,
            actual: $output
        );
    }
}

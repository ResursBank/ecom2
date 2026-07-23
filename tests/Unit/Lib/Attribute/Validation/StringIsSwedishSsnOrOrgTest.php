<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Lib\Attribute\Validation;

use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use ReflectionParameter;
use Resursbank\Ecom\Lib\Attribute\Validation\StringIsSwedishSsnOrOrg;

/**
 * Tests for StringIsSwedishSsnOrOrg
 */
#[AllowMockObjectsWithoutExpectations]
class StringIsSwedishSsnOrOrgTest extends TestCase
{
    /**
     * Verify behavior of getAcceptedValues.
     *
     * @throws Exception
     */
    public function testGetAcceptedValues(): void
    {
        $counts = [];

        for ($i = 0; $i < 10; $i++) {
            $counts[] = rand(min: 1, max: 100);
        }

        $validator = new StringIsSwedishSsnOrOrg();

        foreach ($counts as $count) {
            $parameter = $this->createMock(type: ReflectionParameter::class);

            $output = $validator->getAcceptedValues($parameter, $count);

            $this->assertCount(expectedCount: $count, haystack: $output);

            foreach ($output as $value) {
                $this->assertEquals(expected: '198305147715', actual: $value);
            }
        }
    }

    /**
     * Verify behavior of getRejectedValues.
     *
     * @throws Exception
     */
    public function testGetRejectedValues(): void
    {
        $counts = [];

        for ($i = 0; $i < 10; $i++) {
            $counts[] = rand(min: 1, max: 100);
        }

        $validator = new StringIsSwedishSsnOrOrg();

        foreach ($counts as $count) {
            $parameter = $this->createMock(type: ReflectionParameter::class);

            $output = $validator->getRejectedValues($parameter, $count);

            $this->assertCount(expectedCount: $count, haystack: $output);

            foreach ($output as $value) {
                $this->assertEquals(
                    expected: '19830dge5147715',
                    actual: $value
                );
            }
        }
    }
}

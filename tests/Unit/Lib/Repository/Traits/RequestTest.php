<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Lib\Repository\Traits;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Resursbank\Ecom\Lib\Collection\Collection;
use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Repository\Traits\Request;
use stdClass;

/**
 * Test class for validateCustomModelConverter method in Request trait
 *
 * @phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
#[AllowMockObjectsWithoutExpectations]
final class RequestTest extends TestCase
{
    private Request $request;

    /**
     * This method is called before each test.
     */
    protected function setUp(): void
    {
        // Create a mock Request instance for testing.
        $this->request = $this->getMockBuilder(className: Request::class)
            ->disableOriginalConstructor()
            ->onlyMethods(methods: [])
            ->getMock();

        parent::setUp();
    }

    /**
     * Test that validation passes with a valid closure returning Collection|Model.
     *
     * @throws ReflectionException
     */
    public function testValidationPassesWithValidClosure(): void
    {
        /* @phpstan-ignore-next-line */
        $validClosure = fn (stdClass $data): Collection|Model => $this->createMock(
            type: Model::class
        );

        // Should not throw any exception.
        $this->request->validateCustomModelConverter(callable: $validClosure);

        // Assertion to confirm test passed
        $this->assertTrue(condition: true);
    }

    /**
     * Test that validation fails when closure doesn't return Collection|Model.
     *
     * @throws ReflectionException
     */
    public function testValidationFailsWithInvalidReturnType(): void
    {
        $this->expectException(exception: InvalidArgumentException::class);
        $this->expectExceptionMessage(
            message: 'customModelConverter signature must allow for exactly two return types: Collection and Model.'
        );

        $invalidClosure = static fn (stdClass $data): string => 'invalid';

        $this->request->validateCustomModelConverter(callable: $invalidClosure);
    }

    /**
     * Test that validation fails when closure returns only Collection.
     *
     * @throws ReflectionException
     */
    public function testValidationFailsWithOnlyCollectionReturn(): void
    {
        $this->expectException(exception: InvalidArgumentException::class);
        $this->expectExceptionMessage(
            message: 'customModelConverter signature must allow for exactly two return types: Collection and Model.'
        );

        $invalidClosure = fn (stdClass $data): Collection => $this->createMock(
            type: Collection::class
        );

        $this->request->validateCustomModelConverter(callable: $invalidClosure);
    }

    /**
     * Test that validation fails when closure returns only Model.
     *
     * @throws ReflectionException
     */
    public function testValidationFailsWithOnlyModelReturn(): void
    {
        $this->expectException(exception: InvalidArgumentException::class);
        $this->expectExceptionMessage(
            message: 'customModelConverter signature must allow for exactly two return types: Collection and Model.'
        );

        $invalidClosure = fn (stdClass $data): Model => $this->createMock(
            type: Model::class
        );

        $this->request->validateCustomModelConverter(callable: $invalidClosure);
    }

    /**
     * Test that validation fails when closure has no parameters.
     *
     * @throws ReflectionException
     */
    public function testValidationFailsWithNoParameters(): void
    {
        $this->expectException(exception: InvalidArgumentException::class);
        $this->expectExceptionMessage(
            message: 'customModelConverter must accept exactly one argument.'
        );

        /* @phpstan-ignore-next-line */
        $invalidClosure = fn (): Collection|Model => $this->createMock(
            type: Model::class
        );

        $this->request->validateCustomModelConverter(callable: $invalidClosure);
    }

    /**
     * Test that validation fails when closure has multiple parameters.
     *
     * @throws ReflectionException
     */
    public function testValidationFailsWithMultipleParameters(): void
    {
        $this->expectException(exception: InvalidArgumentException::class);
        $this->expectExceptionMessage(
            message: 'customModelConverter must accept exactly one argument.'
        );

        /* @phpstan-ignore-next-line */
        $invalidClosure = fn (stdClass $data, string $extra): Collection|Model => $this->createMock(
            type: Model::class
        );

        $this->request->validateCustomModelConverter(callable: $invalidClosure);
    }

    /**
     * Test that validation fails when parameter is not named 'data'.
     *
     * @throws ReflectionException
     */
    public function testValidationFailsWithWrongParameterName(): void
    {
        $this->expectException(exception: InvalidArgumentException::class);
        $this->expectExceptionMessage(
            message: 'customModelConverter must accept a single argument named "data" of type stdClass.'
        );

        /* @phpstan-ignore-next-line */
        $invalidClosure = fn (stdClass $wrongName): Collection|Model => $this->createMock(
            type: Model::class
        );

        $this->request->validateCustomModelConverter(callable: $invalidClosure);
    }

    /**
     * Test that validation fails when parameter is not of type stdClass.
     *
     * @throws ReflectionException
     */
    public function testValidationFailsWithWrongParameterType(): void
    {
        $this->expectException(exception: InvalidArgumentException::class);
        $this->expectExceptionMessage(
            message: 'customModelConverter must accept a single argument named "data" of type stdClass.'
        );

        /* @phpstan-ignore-next-line */
        $invalidClosure = fn (array $data): Collection|Model => $this->createMock(
            type: Model::class
        );

        $this->request->validateCustomModelConverter(callable: $invalidClosure);
    }

    /**
     * Test that validation fails when parameter has no type hint.
     *
     * @throws ReflectionException
     */
    public function testValidationFailsWithNoTypeHint(): void
    {
        $this->expectException(exception: InvalidArgumentException::class);
        $this->expectExceptionMessage(
            message: 'customModelConverter must accept a single argument named "data" of type stdClass.'
        );

        /* @phpstan-ignore-next-line */
        $invalidClosure = fn ($data): Collection|Model => $this->createMock(
            type: Model::class
        );

        $this->request->validateCustomModelConverter(callable: $invalidClosure);
    }

    /**
     * Test that validation fails when closure has no return type.
     *
     * @throws ReflectionException
     */
    public function testValidationFailsWithNoReturnType(): void
    {
        $this->expectException(exception: InvalidArgumentException::class);
        $this->expectExceptionMessage(
            message: 'customModelConverter signature must allow for exactly two return types: Collection and Model.'
        );

        $invalidClosure = fn (stdClass $data) => $this->createMock(
            type: Model::class
        );

        $this->request->validateCustomModelConverter(callable: $invalidClosure);
    }
}

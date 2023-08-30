<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

/** @noinspection PhpMultipleClassDeclarationsInspection */

declare(strict_types=1);

namespace Resursbank\EcomTest\Integration\Lib\Attribute;

use Exception;
use JsonException;
use PHPUnit\Framework\TestCase;
use ReflectionAttribute;
use ReflectionException;
use ReflectionMethod;
use ReflectionParameter;
use Resursbank\Ecom\Exception\AttributeCombinationException;
use Resursbank\Ecom\Exception\FilesystemException;
use Resursbank\Ecom\Exception\TestException;
use Resursbank\Ecom\Lib\Attribute\Probe;
use Resursbank\Ecom\Lib\Attribute\Validation\ArrayOfStrings;
use Resursbank\Ecom\Lib\Attribute\Validation\ArraySize;
use Resursbank\Ecom\Lib\Attribute\Validation\Interface\AttributeInterface;
use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Utilities\Random;
use Resursbank\EcomTest\Data\Probe\Models\Store;
use Resursbank\EcomTest\Data\Probe\Models\Store\Section\Cleaning;
use Resursbank\EcomTest\Data\Probe\Models\Store\Section\Fruit;
use Resursbank\EcomTest\Data\Probe\Models\Store\Staff;
use Resursbank\EcomTest\Data\Probe\Models\Store\Staff\Boss;
use Resursbank\EcomTest\Data\Probe\Models\Store\Staff\Employee;
use Resursbank\EcomTest\Utilities\DataIntegrity;

/**
 * This class will test Filesystem cache methods.
 *
 * @noinspection EfferentObjectCouplingInspection
 */
class ProbeTest extends TestCase
{
    public const VALIDATION_ITERATIONS = 10;

    /**
     * Describes which class should be utilised to assemble testing values when
     * applying more than one validation attribute to a parameter.
     *
     * For example, when validating an array contains only strings
     * (ArrayOfStrings) and at the same time ensuring a specific size
     * (ArraySize), ArrayOfStrings will be the class to assemble test values.
     * The getAcceptedValues/getRejectedValues
     *
     * @var array<array>
     */
    private static array $validationCombo = [
        ArrayOfStrings::class => [ArrayOfStrings::class, ArraySize::class]
    ];

    /**
     * Resolve attribute to access test values from.
     *
     * @throws AttributeCombinationException
     * @throws JsonException
     * @throws TestException
     */
    private function getValidationAttribute(
        ReflectionParameter $parameter
    ): ?AttributeInterface {
        $attributes = Model::getValidationAttributes(parameter: $parameter);

        if (empty($attributes)) {
            return null;
        }

        return match (count($attributes)) {
            1 => $attributes[0],
            default => $this->getValidationAttributeFromCombination(
                attributes: $attributes
            )
        };
    }

    /**
     * @throws TestException
     * @throws JsonException
     */
    private function getValidationAttributeFromCombination(
        array $attributes
    ): AttributeInterface {
        $map = $this->getAttributeMap(attributes: $attributes);
        $keys = array_keys(array: $map);
        sort($keys);

        foreach (self::$validationCombo as $class => $combo) {
            sort($combo);

            if (isset($map[$class]) && $combo === $keys) {
                return $map[$class];
            }
        }

        throw new TestException(
            message: sprintf(
                'Failed to find matching attribute validation for combination %s',
                json_encode(value: $keys, flags: JSON_THROW_ON_ERROR)
            )
        );
    }

    /**
     * Convert array of ReflectionAttribute objects to actual attribute
     * instances where the key is the name of the attribute class.
     */
    private function getAttributeMap(
        array $attributes
    ): array {
        $result = [];

        /** @var ReflectionAttribute $attribute */
        foreach ($attributes as $attribute) {
            if (!$attribute instanceof AttributeInterface) {
                continue;
            }

            $instance = $attribute->newInstance();
            $result[$instance::class] = $instance;
        }

        return $result;
    }

    /**
     * Get random value for parameter.
     *
     * @throws AttributeCombinationException
     * @throws JsonException
     * @throws TestException
     */
    private function getRandomAcceptedParameterValue(
        ReflectionParameter $parameter
    ): mixed {
        $attribute = $this->getValidationAttribute(parameter: $parameter);

        $accepted = $attribute?->getAcceptedValues(
            parameter: $parameter,
            size: 1
        ) ?? [];

        /** @noinspection OffsetOperationsInspection */
        /** @noinspection PhpArrayIsAlwaysEmptyInspection */
        return !empty($accepted)
            /* @phpstan-ignore-next-line */
            ? $accepted[array_rand(array: $accepted)]
            : null;
    }

    /**
     * @throws ReflectionException
     * @throws TestException
     * @throws Exception
     */
    private function generateModel(
        string $class,
        array $predefined = []
    ): Model {
        $args = [];
        $params = (new ReflectionMethod(
            objectOrMethod: $class,
            method: '__construct'
        ))->getParameters();

        foreach ($params as $p) {
            if (isset($predefined[$p->getName()])) {
                $args[] = $predefined[$p->getName()];
                continue;
            }

            $args[] = $this->getRandomAcceptedParameterValue(parameter: $p) ??
                $this->getDefaultTypeValue(parameter: $p);
        }

        $result = new $class(...$args);

        if (!$result instanceof Model) {
            throw new TestException(message: "$class is not a Model");
        }

        return $result;
    }

    /**
     * Resolve default value based on datatype.
     *
     * @throws Exception
     */
    private function getDefaultTypeValue(
        ReflectionParameter $parameter
    ): mixed {
        if (!$parameter->hasType()) {
            return 0;
        }

        $type = (string) $parameter->getType();

        if (str_contains(haystack: $type, needle: '|')) {
            $type = substr(
                string: $type,
                offset: 0,
                length: (int) strpos(haystack: $type, needle: '|')
            );
        }

        /* @phpstan-ignore-next-line */
        return match ($type) {
            'string' => Random::getString(),
            'int' => Random::getInt(),
            'float' => Random::getFloat(),
            'bool' => Random::getBool(),
            'object' => Random::getObject(),
            'array' => Random::getArray()
        };
    }

    /**
     * @throws AttributeCombinationException
     * @throws JsonException
     * @throws ReflectionException
     * @throws TestException
     * @throws Exception
     */
    private function probeParameter(
        string $class,
        ReflectionParameter $parameter
    ): void {
        // Get validation attributes specified for the Model property.
        $attribute = $this->getValidationAttribute(parameter: $parameter);

        // Randomize accepted values to test.
        $accepted = $attribute?->getAcceptedValues(
            parameter: $parameter,
            size: self::VALIDATION_ITERATIONS
        ) ?? [$this->getDefaultTypeValue(parameter: $parameter)];

        // Randomize rejected values to test.
        $rejected = $attribute?->getRejectedValues(
            parameter: $parameter,
            size: self::VALIDATION_ITERATIONS
        ) ?? [];

        // Add nullable check if eligible.
        if ($parameter->allowsNull()) {
            $accepted[] = null;
        }

        // Add default value check if eligible.
        if ($parameter->isDefaultValueAvailable()) {
            $accepted[] = $parameter->getDefaultValue();
        }

        /* Make sure we expect at least one assertion to be made. If the
           parameter has no validation attribute, a single random value
           will be tested based on property type. */
        $this->assertNotEmpty(actual: $accepted);

        if ($attribute !== null) {
            $this->assertGreaterThanOrEqual(
                expected: self::VALIDATION_ITERATIONS,
                actual: count($rejected),
                message: sprintf(
                    'Seems we fail to generate random rejected values for %s',
                    $attribute::class
                )
            );

            $this->assertGreaterThanOrEqual(
                expected: self::VALIDATION_ITERATIONS,
                actual: count($accepted),
                message: sprintf(
                    'Seems we fail to generate random accepted values for %s',
                    $attribute::class
                )
            );
        }

        $assertionCount = $this->getNumAssertions();

        // Test all accepted & rejected values one at a time.
        DataIntegrity::testValueIntegrity(
            accepted: $accepted,
            rejected: $rejected,
            test: $this,
            callback: fn (mixed $v) => $this->generateModel(
                class: $class,
                predefined: [$parameter->name => $v]
            ),
            class: $class,
            parameter: $parameter->getName()
        );

        // Make sure that any DataIntegrity tests were conducted.
        $this->assertGreaterThan(
            expected: $assertionCount,
            actual: $this->getNumAssertions()
        );

        // Make sure that all DataIntegrity tests were conducted.
        $this->assertSame(
            expected: $assertionCount + count($rejected) + count(
                $accepted
            ),
            actual: $this->getNumAssertions()
        );
    }

    /**
     * @throws FilesystemException
     */
    public function testGetClasses(): void
    {
        $expected = [
            Store::class,
            Employee::class,
            Boss::class,
            Staff::class,
            Cleaning::class,
            Fruit::class,
        ];
        $classes = Probe::getClasses(dir: 'tests/Data/Probe');

        self::assertCount(expectedCount: count($expected), haystack: $classes);
        self::assertEquals(expected: $expected, actual: $classes);

        foreach ($classes as $classname) {
            self::assertContains(needle: $classname, haystack: $expected);
        }
    }

    /**
     * Assert that automatically creating instances of Probable classes work,
     * using default values where applicable, and resolving random accepted
     * values from attribute classes where not, where neither default value
     * is resolved from parameter type.
     *
     * @throws AttributeCombinationException
     * @throws FilesystemException
     * @throws JsonException
     * @throws ReflectionException
     * @throws TestException
     * @throws Exception
     */
    public function testGenerateModels(): void
    {
        // Get all classes where the #[Probable] has been specified from path.
        foreach (Probe::getClasses(dir: 'tests/Data/Probe') as $class) {
            // Get all parameters from the class constructor (Model properties).
            $params = (new ReflectionMethod(
                objectOrMethod: $class,
                method: '__construct'
            )
            )->getParameters();

            foreach ($params as $parameter) {
                $this->probeParameter(class: $class, parameter: $parameter);
            }
        }
    }
}

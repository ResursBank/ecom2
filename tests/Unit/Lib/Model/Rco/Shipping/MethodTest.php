<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Lib\Model\Rco\Shipping;

use PHPUnit\Framework\TestCase;
use ReflectionException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Lib\Model\Rco\Enum\Required;
use Resursbank\Ecom\Lib\Model\Rco\Enum\RequiredCollection;
use Resursbank\Ecom\Lib\Model\Rco\Shipping\Carrier;
use Resursbank\Ecom\Lib\Model\Rco\Shipping\Method;
use Resursbank\Ecom\Lib\Model\Rco\Shipping\OptionCollection;
use Resursbank\Ecom\Lib\Model\Rco\Shipping\Price;
use Resursbank\Ecom\Lib\Model\Rco\Shipping\Type;
use Resursbank\EcomTest\Data\Enum\Trash;
use Resursbank\EcomTest\Utilities\DataIntegrity;
use Throwable;

/**
 * Integrity test of RCO Checkout Shipping Method model class.
 */
class MethodTest extends TestCase
{
    /**
     * Get mocked model instance.
     *
     * @throws IllegalTypeException
     */
    private function generateModel(
        ?array $required = null
    ): void {
        new Method(
            methodId: 'my-method',
            name: 'My Method',
            type: Type::DELIVERY,
            carrier: Carrier::GENERIC,
            description: 'the best shipping method',
            price: new Price(
                display: '100 SEK',
                calculate: 80,
                calculateTax: 25
            ),
            deliveryEta: 'Immediately',
            options: new OptionCollection(data: []),
            required: new RequiredCollection(
                data: $required ?? Required::cases()
            )
        );
    }

    /**
     * Test generating a valid model instance.
     */
    public function testModel(): void
    {
        try {
            $this->generateModel();
            $this->addToAssertionCount(count: 1);
        } catch (Throwable) {
            $this->fail(message: 'Failed to generate Options model instance.');
        }
    }

    /**
     * Assert that the supplied values in the required array are converted from
     * strings to their enum counterpart.
     *
     * @throws ReflectionException
     */
    public function testRequiredEvaluation(): void
    {
        DataIntegrity::testValueIntegrity(
            accepted: [
                ['EMAIL'],
                ['EMAIL', 'PHONE'],
                [],
                [Required::ADDRESS],
                [Required::NAME, 'GOVERNMENT_ID'],
                Required::cases()
            ],
            rejected: [
                ['YODA'],
                [Required::ADDRESS, 'GOVERNMENT_ID', 'TESTING'],
                Trash::cases()
            ],
            callback: fn (array $v) => $this->generateModel(required: $v),
            test: $this
        );
    }
}

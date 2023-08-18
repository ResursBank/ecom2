<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Lib\Model\Rco;

use PHPUnit\Framework\TestCase;
use ReflectionException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Lib\Model\Rco\Enum\Required;
use Resursbank\Ecom\Lib\Model\Rco\Enum\RequiredCollection;
use Resursbank\Ecom\Lib\Model\Rco\PaymentMethod;
use Resursbank\Ecom\Lib\Model\Rco\PaymentMethod\LinkCollection;
use Resursbank\Ecom\Lib\Model\Rco\PaymentMethod\Type;
use Resursbank\EcomTest\Data\Enum\Trash;
use Resursbank\EcomTest\Utilities\DataIntegrity;
use Throwable;

/**
 * Integrity test of RCO Checkout PaymentMethod model class.
 */
class PaymentMethodTest extends TestCase
{
    /**
     * Get mocked model instance.
     *
     * @throws IllegalTypeException
     */
    private function generateModel(
        ?array $required = null
    ): void {
        new PaymentMethod(
            methodId: '',
            name: '',
            type: Type::GENERIC,
            fee: 0,
            required: new RequiredCollection(
                data: $required ?? Required::cases()
            ),
            subtitle: '',
            descriptions: [],
            terms: '',
            links: new LinkCollection(data: [])
        );
    }

    /**
     * Test generating a valid model instance.
     */
    public function testPaymentMethodModel(): void
    {
        try {
            $this->generateModel();
            $this->addToAssertionCount(count: 1);
        } catch (Throwable) {
            $this->fail(message: 'Failed to generate model instance.');
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

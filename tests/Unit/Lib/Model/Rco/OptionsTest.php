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
use Resursbank\Ecom\Lib\Model\Rco\Options;
use Resursbank\EcomTest\Data\Enum\Trash;
use Resursbank\EcomTest\Utilities\DataIntegrity;
use Throwable;

/**
 * Integrity test of RCO Checkout Options model class.
 */
class OptionsTest extends TestCase
{
    /**
     * Get mocked model instance.
     *
     * @throws IllegalTypeException
     */
    private function generateModel(
        ?array $requiredFields = null
    ): void {
        new Options(
            renderCart: true,
            calculateShipping: true,
            lookupB2CAddress: true,
            renderCartCode: true,
            renderNotes: true,
            allowDelayedAuthorization: true,
            requiredFields: new RequiredCollection(
                data: $requiredFields ?? Required::cases()
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
     * Assert that the supplied values in the requiredFields array are converted
     * from strings to their enum counterpart.
     *
     * @throws ReflectionException
     */
    public function testRequiredFieldsEvaluation(): void
    {
        DataIntegrity::testValueIntegrity(
            accepted: [
                ['EMAIL'],
                ['EMAIL', 'PHONE'],
                [],
                [Required::ADDRESS],
                [Required::NAME, 'GOVERNMENT_ID'],
                Required::cases(),
                null
            ],
            rejected: [
                ['YODA'],
                [Required::ADDRESS, 'GOVERNMENT_ID', 'TESTING'],
                Trash::cases()
            ],
            callback: fn (?array $v) => $this->generateModel(
                requiredFields: $v
            ),
            test: $this
        );
    }
}

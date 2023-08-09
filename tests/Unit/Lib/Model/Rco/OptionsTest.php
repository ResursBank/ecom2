<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Lib\Model\Rco;

use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Lib\Model\Rco\Options;
use Resursbank\Ecom\Lib\Model\Rco\Enum\Required;
use Throwable;

/**
 * Integrity test of RCO Checkout Options model class.
 */
class OptionsTest extends TestCase
{
    /**
     * Get mocked model instance.
     */
    private function generateModel(
        ?array $requiredFields = null
    ): void {
        new Options(
            requiredFields: $requiredFields ?? Required::cases()
        );
    }

    /**
     * Test generating a valid model instance.
     */
    public function testOptionsModel(): void
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
     */
    public function testRequiredFieldsEvaluation(): void
    {
        try {
            $this->generateModel(
                requiredFields: ['EMAIL']
            );

            $this->addToAssertionCount(count: 1);
        } catch (Throwable) {
            $this->fail(message: 'String EMAIL was not converted to RequiredFields::EMAIL');
        }

        try {
            $this->generateModel(
                requiredFields: ['EMAIL', 'PHONE']
            );

            $this->addToAssertionCount(count: 1);
        } catch (Throwable) {
            $this->fail(message: 'String EMAIL or PHONE was not converted to RequiredFields case.');
        }

        try {
            $this->generateModel(
                requiredFields: ['YODA']
            );

            $this->fail(message: 'String YODA was converted to none existent case in RequiredFields.');
        } catch (Throwable) {
            $this->addToAssertionCount(count: 1);
        }

        try {
            $this->generateModel(
                requiredFields: [123]
            );

            $this->fail(message: 'Int 123 was converted to none existent case in RequiredFields.');
        } catch (Throwable) {
            $this->addToAssertionCount(count: 1);
        }

        try {
            $this->generateModel(
                requiredFields: [Required::ADDRESS]
            );

            $this->addToAssertionCount(count: 1);
        } catch (Throwable) {
            $this->fail(message: 'RequiredFields case ADDRESS was rejected.');
        }

        try {
            $this->generateModel(
                requiredFields: [Required::NAME, 'GOVERNMENT_ID']
            );

            $this->addToAssertionCount(count: 1);
        } catch (Throwable) {
            $this->fail(message: 'Mixed RequiredFields case and string was rejected.');
        }

        try {
            $this->generateModel(
                requiredFields: [Required::ADDRESS, 'GOVERNMENT_ID', 123]
            );

            $this->fail(message: 'Mixing RequiredFields case, string and int was accepted.');
        } catch (Throwable) {
            $this->addToAssertionCount(count: 1);
        }
    }
}

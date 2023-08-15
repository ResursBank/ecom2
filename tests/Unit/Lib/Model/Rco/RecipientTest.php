<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Lib\Model\Rco;

use Exception;
use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Model\Rco\Recipient;
use Resursbank\Ecom\Lib\Utilities\Strings;
use Throwable;

use function strlen;

/**
 * Integrity test of RCO Checkout Recipient model class.
 */
class RecipientTest extends TestCase
{
    /**
     * Get mocked model instance.
     *
     * @throws IllegalValueException
     */
    private function generateModel(
        ?string $name = null
    ): void {
        new Recipient(name: $name);
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
            $this->fail(
                message: 'Failed to generate Recipient model instance.'
            );
        }
    }

    /**
     * Assert integrity of name property.
     *
     * @throws Exception
     */
    public function testValidateName(): void
    {
        $value1 = Strings::generateRandomString(length: 100);
        $value2 = Strings::generateRandomString(length: 128);
        $value3 = Strings::generateRandomString(length: 129);

        try {
            $this->generateModel(name: $value1);
            $this->addToAssertionCount(count: 1);
        } catch (IllegalValueException) {
            $this->fail(
                message: sprintf(
                    'Name did not accept random value %s (count %s)',
                    $value1,
                    strlen(string: $value1)
                )
            );
        }

        try {
            $this->generateModel(name: $value2);
            $this->addToAssertionCount(count: 1);
        } catch (IllegalValueException) {
            $this->fail(
                message: sprintf(
                    'Name did not accept random value %s (count %s)',
                    $value2,
                    strlen(string: $value2)
                )
            );
        }

        try {
            $this->generateModel(name: $value3);

            $this->fail(
                message: sprintf(
                    'Name accepted a value exceeding 128 characters, %s',
                    $value3
                )
            );
        } catch (IllegalValueException) {
            $this->addToAssertionCount(count: 1);
        }

        try {
            $this->generateModel(name: '');
            $this->addToAssertionCount(count: 1);
        } catch (Throwable) {
            $this->fail(message: 'Name did not accept empty string.');
        }
    }
}

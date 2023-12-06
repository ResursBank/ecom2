<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Lib\Model\Rco;

use JsonException;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Resursbank\Ecom\Exception\AttributeCombinationException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Lib\Model\Rco\Enum\AvailableActions;
use Resursbank\Ecom\Lib\Model\Rco\Enum\AvailableActionsCollection;
use Resursbank\Ecom\Lib\Model\Rco\Enum\PaymentStatus as PaymentStatusEnum;
use Resursbank\Ecom\Lib\Model\Rco\PaymentStatus;
use Resursbank\EcomTest\Data\Enum\Trash;
use Resursbank\EcomTest\Utilities\DataIntegrity;
use Throwable;

use function is_array;

/**
 * Integrity test of RCO Checkout PaymentStatus model class.
 */
class PaymentStatusTest extends TestCase
{
    /**
     * Get mocked model instance.
     *
     * @throws IllegalTypeException
     * @throws ReflectionException
     * @throws JsonException
     * @throws AttributeCombinationException
     */
    private function generateModel(
        ?array $availableActions = null
    ): void {
        new PaymentStatus(
            requestedAmount: 0,
            authorizedAmount: 0,
            cancelledAmount: 0,
            capturedAmount: 0,
            refundedAmount: 0,
            availableActions: is_array(value: $availableActions) ?
                new AvailableActionsCollection(
                    data: $availableActions
                ) : new AvailableActionsCollection(
                    data: []
                ),
            type: PaymentStatusEnum::AUTHORIZED
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
     * Assert that the supplied values in the availableActions array are
     * converted from strings to their enum counterpart.
     *
     * @throws ReflectionException
     */
    public function testAvailableActionsEvaluation(): void
    {
        DataIntegrity::testValueIntegrity(
            accepted: [
                ['REFUND'],
                ['CAPTURE', 'CANCEL'],
                [],
                [AvailableActions::CAPTURE],
                [AvailableActions::REFUND, 'CANCEL'],
                null,
                AvailableActions::cases()
            ],
            rejected: [
                ['YODA'],
                [AvailableActions::CANCEL, Trash::FOOD],
                [AvailableActions::CAPTURE, 'HELLO'],
                Trash::cases()
            ],
            callback: fn (?array $v) => $this->generateModel(
                availableActions: $v
            ),
            test: $this
        );
    }
}

<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

/** @noinspection PhpMultipleClassDeclarationsInspection */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Module\PaymentMethod\Models\PaymentMethod;

use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Lib\Model\PaymentMethod\LegalLink;

/**
 * Test data integrity of legal link object attached to payment methods.
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
class LegalLinkTest extends TestCase
{
    /**
     * Assert that a legal link can't be created with an empty url supplied.
     *
     * @return void
     * @todo $type validation to be replaced by Enum\LegalLink\Type when supported by DataConverter.
     */
    public function testValidateUrlThrowsWithEmpty(): void
    {
        $this->expectException(exception: EmptyValueException::class);
        new LegalLink(
            url: '',
            type: 'GENERAL_TERMS',
            needToAppendPriceLast: false
        );
    }
}

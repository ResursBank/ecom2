<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Lib\Model\Rco;

use Exception;
use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Exception\Validation\MissingValueException;
use Resursbank\Ecom\Lib\Model\Rco\Customer\Type as CustomerType;
use Resursbank\Ecom\Lib\Model\Rco\Customer\TypeCollection;
use Resursbank\Ecom\Lib\Model\Rco\Enum\RequiredCollection;
use Resursbank\Ecom\Lib\Model\Rco\PaymentMethod;
use Resursbank\Ecom\Lib\Model\Rco\PaymentMethod\LinkCollection;
use Resursbank\Ecom\Lib\Model\Rco\PaymentMethod\Type;
use Resursbank\Ecom\Lib\Model\Rco\PaymentMethodCollection;
use Resursbank\Ecom\Lib\Utilities\Strings;

/**
 * Integrity test of RCO Checkout PaymentMethodCollection model class.
 */
class PaymentMethodCollectionTest extends TestCase
{
    /**
     * Get mocked model instance.
     *
     * @throws IllegalTypeException
     */
    private function generateModel(
        string $id,
        string $name
    ): PaymentMethod {
        return new PaymentMethod(
            methodId: $id,
            name: $name,
            type: Type::GENERIC,
            fee: 0,
            required: new RequiredCollection(data: []),
            subtitle: '',
            descriptions: [],
            terms: '',
            links: new LinkCollection(data: []),
            customerTypes: new TypeCollection(
                data: [CustomerType::B2C, CustomerType::B2B]
            ),
            minLimit: 10,
            maxLimit: 50000
        );
    }

    /**
     * Assert payment method name resolution (defaults to id).
     *
     * @throws IllegalTypeException
     */
    public function testGetMethodName(): void
    {
        $collection = new PaymentMethodCollection(data: [
            $this->generateModel(id: 'test', name: 'Test'),
            $this->generateModel(id: 'some-method', name: 'Some method'),
            $this->generateModel(id: 'same-method', name: 'Same Method'),
        ]);

        $this->assertSame(
            expected: 'Some method',
            actual: $collection->getMethodName(methodId: 'some-method')
        );

        $this->assertSame(
            expected: 'does-not-exist',
            actual: $collection->getMethodName(methodId: 'does-not-exist')
        );
    }

    /**
     * Assert we can search collection for payment method by id.
     *
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws MissingValueException
     * @throws Exception
     */
    public function testGetById(): void
    {
        $id1 = Strings::getUuid();
        $name1 = Strings::generateRandomString(10);

        $id2 = Strings::getUuid();
        $name2 = Strings::generateRandomString(10);

        $id3 = Strings::getUuid();
        $name3 = Strings::generateRandomString(10);

        $collection = new PaymentMethodCollection(data: [
            $this->generateModel($id1, $name1),
            $this->generateModel($id2, $name2),
            $this->generateModel($id3, $name3),
        ]);

        $this->assertEquals($id1, $collection->getById($id1)->getId());
        $this->assertEquals($id2, $collection->getById($id2)->getId());
        $this->assertEquals($id3, $collection->getById($id3)->getId());
    }

    /**
     * Assert that getById throws exception if method is not found.
     *
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws MissingValueException
     */
    public function testGetByIdThrows(): void
    {
        $this->expectException(MissingValueException::class);

        $collection = new PaymentMethodCollection(data: []);
        $collection->getById(Strings::getUuid());
    }
}

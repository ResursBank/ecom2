<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Module\Widget\Logo;

use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\FilesystemException;
use Resursbank\Ecom\Lib\Api\GrantType;
use Resursbank\Ecom\Lib\Cache\Filesystem;
use Resursbank\Ecom\Lib\Log\LoggerInterface;
use Resursbank\Ecom\Lib\Model\Network\Auth\Jwt;
use Resursbank\Ecom\Lib\Model\PaymentMethod;
use Resursbank\Ecom\Lib\Order\PaymentMethod\Type;
use Resursbank\Ecom\Lib\Utilities\Strings;
use Resursbank\Ecom\Module\Widget\Logo\Html;

/**
 * Test for the Logo Html widget.
 */
#[AllowMockObjectsWithoutExpectations]
class HtmlTest extends TestCase
{
    protected function setUp(): void
    {
        Config::setup(
            logger: $this->createMock(
                type: LoggerInterface::class
            ),
            cache: new Filesystem(path: '/tmp/ecom-test/customer/' . time()),
            jwtAuth: new Jwt(
                clientId: $_ENV['JWT_AUTH_CLIENT_ID'],
                clientSecret: $_ENV['JWT_AUTH_CLIENT_SECRET'],
                grantType: GrantType::from(value: $_ENV['JWT_AUTH_GRANT_TYPE'])
            ),
            storeId: $_ENV['STORE_ID']
        );
    }

    private function getDummyPaymentMethod(Type $type): PaymentMethod
    {
        return new PaymentMethod(
            id: Strings::getUuid(),
            name: Strings::generateRandomString(length: 10),
            type: $type,
            minPurchaseLimit: 0.0,
            maxPurchaseLimit: 42.0,
            minApplicationLimit: 0.0,
            maxApplicationLimit: 42.0,
            legalLinks: $this->createMock(
                type: PaymentMethod\LegalLinkCollection::class
            ),
            enabledForLegalCustomer: true,
            enabledForNaturalCustomer: true,
            priceSignagePossible: true
        );
    }

    /**
     * Verify that the constructor method type to file mapping is correct.
     *
     * @throws FilesystemException
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public function testFileMatch(): void
    {
        $matchTable = [
            Type::SWISH->value => 'swish.png',
            Type::DEBIT_CARD->value => 'card.png',
            Type::CREDIT_CARD->value => 'card.png',
            Type::INTERNET->value => 'trustly.png'
        ];

        foreach (Type::cases() as $type) {
            $method = $this->getDummyPaymentMethod(type: $type);
            $widget = new Html(paymentMethod: $method);

            if (array_key_exists(key: $type->value, array: $matchTable)) {
                $file = $matchTable[$type->value];
            } else {
                $file = 'resurs.png';
            }

            $this->assertEquals(expected: $file, actual: $widget->file);
        }
    }

    /**
     * Verify that getIdentifier strips file extension.
     *
     * @throws FilesystemException
     */
    public function testGetIdentifier(): void
    {
        $method = $this->getDummyPaymentMethod(type: Type::SWISH);
        $widget = new Html(paymentMethod: $method);
        $this->assertEquals(
            expected: 'swish',
            actual: $widget->getIdentifier()
        );
    }

    /**
     * Verify that the incImgTag parameter is handled properly.
     *
     * @throws FilesystemException
     */
    public function testGetLogo(): void
    {
        $method = $this->getDummyPaymentMethod(type: Type::SWISH);
        $widget = new Html(paymentMethod: $method);

        $base64 = $widget->getLogo();
        $this->assertStringStartsWith(
            prefix: '<img src="data:image/png;base64,',
            string: $base64
        );

        $base64NoImgTag = $widget->getLogo(inclImgTag: false);
        $this->assertStringStartsWith(
            prefix: 'data:image/png;base64,',
            string: $base64NoImgTag
        );
    }
}

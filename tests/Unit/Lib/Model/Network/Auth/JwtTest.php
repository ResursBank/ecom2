<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Lib\Model\Network\Auth;

use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Lib\Model\Network\Auth\Jwt;

/**
 * Tests for JWT credentials model.
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
class JwtTest extends TestCase
{
    /**
     * Assert EmptyValueException is thrown when clientId is empty.
     *
     * @return void
     */
    public function testThrowsOnEmptyClientId(): void
    {
        $this->expectException(exception: EmptyValueException::class);

        new Jwt(
            clientId: '',
            clientSecret: 'secret',
            scope: 'scope',
            grantType: 'grantType'
        );
    }

    /**
     * Assert EmptyValueException is thrown when clientSecret is empty.
     *
     * @return void
     */
    public function testThrowsOnEmptyClientSecret(): void
    {
        $this->expectException(exception: EmptyValueException::class);

        new Jwt(
            clientId: 'clientId',
            clientSecret: '',
            scope: 'scope',
            grantType: 'grantType'
        );
    }

    /**
     * Assert EmptyValueException is thrown when scope is empty.
     *
     * @return void
     */
    public function testThrowsOnEmptyScope(): void
    {
        $this->expectException(exception: EmptyValueException::class);

        new Jwt(
            clientId: 'clientId',
            clientSecret: 'secret',
            scope: '',
            grantType: 'grantType'
        );
    }

    /**
     * Assert EmptyValueException is thrown when grantType is empty.
     *
     * @return void
     */
    public function testThrowsOnEmptyGrantType(): void
    {
        $this->expectException(exception: EmptyValueException::class);

        new Jwt(
            clientId: 'clientId',
            clientSecret: 'secret',
            scope: 'scope',
            grantType: ''
        );
    }
}

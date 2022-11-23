<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

/** @noinspection PhpMultipleClassDeclarationsInspection */

declare(strict_types=1);

namespace Resursbank\EcomTest\Utilities;

use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Lib\Utilities\Session;

/**
 * Methods to spawn a mocked session handler.
 */
trait MockSessionTrait
{
    /**
     * @var Session
     */
    private Session $session;

    /**
     * PHPUnit sends headers before this executes, thus we cannot manipulate
     * our session handler (starting / stopping it to check the behaviour
     * of our methods). We mock the isAvailable method to fix that.
     *
     * @param TestCase $test
     * @return void
     */
    public function setupSession(
        TestCase $test
    ): void {
        // Clear session data.
        unset($_SESSION);

        $this->session = $test->createPartialMock(
            originalClassName: Session::class,
            methods: ['isAvailable']
        );
    }

    /**
     * Make session appear enabled.
     *
     * @return void
     * @noinspection UnnecessaryAssertionInspection
     * @psalm-suppress
     */
    public function enableSession(): void
    {
        $this->session
            ->expects($this->any())
            ->method(constraint: 'isAvailable')
            ->willReturn(value: true);
    }

    /**
     * Make session appear disabled.
     *
     * @return void
     * @noinspection UnnecessaryAssertionInspection
     * @psalm-suppress
     */
    public function disableSession(): void
    {
        $this->session
            ->expects($this->any())
            ->method(constraint: 'isAvailable')
            ->willReturn(value: false);
    }
}

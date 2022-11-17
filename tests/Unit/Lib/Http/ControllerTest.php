<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Lib\Http;

use Exception;
use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\HttpException;
use Resursbank\Ecom\Lib\Http\Controller;
use Resursbank\Ecom\Lib\Locale\Locale;

/**
 * Test basic controller methods.
 */
class ControllerTest extends TestCase
{
    private Controller $controller;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        Config::setup(locale: Locale::en);

        $this->controller = new Controller();
    }

    /**
     * Assert respond() will echo JSON encoded data from supplied array.
     *
     * @return void
     * @SuppressWarnings(PHPMD.ErrorControlOperator)
     */
    public function testRespondContent(): void
    {
        ob_start();

        // PHPUnit does not support testing code that manipulate headers.
        /** @noinspection PhpUsageOfSilenceOperatorInspection */
        @$this->controller->respond(
            data: ['data' => 'aha']
        );
        $result = ob_get_clean();

        $this->assertSame(
            expected: '{"data":"aha"}',
            actual: $result,
            message: 'Unexpected output data.'
        );
    }

    /**
     * Assert getErrorMessage() returns unmasked error message for HttpException.
     *
     * @return void
     */
    public function testGetErrorMessageReturnsUnmasked(): void
    {
        $this->assertSame(
            expected: 'My Message',
            actual: $this->controller->getErrorMessage(
                exception: new HttpException(message: 'My Message')
            ),
            message: 'Error message is not unmasked.'
        );
    }

    /**
     * Test getErrorMessage() will mask Exception messages.
     *
     * @return void
     */
    public function testGetErrorMessageReturnsMasked(): void
    {
        $this->assertSame(
            expected: 'An unknown error occurred.',
            actual: $this->controller->getErrorMessage(
                exception: new Exception(message: 'My Message')
            )
        );
    }

    /**
     * Assert getPostParam() throws HttpException with code 404 if the
     * requested key is not defined.
     *
     * @return void
     * @throws HttpException
     */
    public function testGetPostParamThrowsWhenMissing(): void
    {
        $this->expectException(exception: HttpException::class);
        $this->expectExceptionCode(code: 404);

        $this->controller->getPostParam(
            param: 'something'
        );
    }

    /**
     * Assert getPostParam() throws HttpException with code 411 if the specified
     * key is empty.
     *
     * @return void
     * @throws HttpException
     */
    public function testGetPostParamThrowsWhenEmpty(): void
    {
        $this->expectException(exception: HttpException::class);
        $this->expectExceptionCode(code: 411);

        $_POST['something'] = '';

        $this->controller->getPostParam(
            param: 'something'
        );
    }

    /**
     * Assert getPostParam() throws HttpException with code 411 if the specified
     * key is not a string.
     *
     * @return void
     * @throws HttpException
     */
    public function testGetPostParamThrowsWithoutString(): void
    {
        $this->expectException(exception: HttpException::class);
        $this->expectExceptionCode(code: 411);

        $_POST['something'] = 211;

        $this->controller->getPostParam(
            param: 'something'
        );
    }

    /**
     * Assert getPostParam() method returns value of specified key.
     *
     * @return void
     * @throws HttpException
     */
    public function testGetPostParamReturns(): void
    {
        $_POST['something'] = 'else';

        $this->assertSame(
            expected: 'else',
            actual: $this->controller->getPostParam(
                param: 'something'
            ),
            message: 'Failed to extract value from POST variable.'
        );
    }

    /**
     * Assert log() does not throw Exceptions.
     *
     * @return void
     */
    public function testLogIsSilent(): void
    {
        Config::unsetInstance();

        $this->expectNotToPerformAssertions();
        $this->controller->log(exception: new Exception(message: 'Nothing'));
    }

    /**
     * Assert translateError() provides defaulted message if translation fails.
     *
     * @return void
     */
    public function testDefaultError(): void
    {
        $this->assertSame(
            expected: 'Failed to translate error. Check debug log for info.',
            actual: $this->controller->translateError(phraseId: 'some-undefined-translation-995'),
            message: 'Unexpected default error message.'
        );
    }
}

<?php

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Lib\Model\Rco;

use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Exception\Validation\IllegalUrlException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Model\Rco\Webhook;

class WebhookTest extends TestCase
{
    /**
     * Validate a bad url as bad.
     */
    public function testBadWebHook(): void
    {
        self::expectException(exception: IllegalUrlException::class);
        new Webhook(
            url: 'hppt://www.test.com',
            authorization: '',
            continueOnNoResponse: false,
            timeout: 0
        );
    }

    /**
     * Validate correct url.
     */
    public function testWebHook(): void
    {
        self::assertInstanceOf(
            expected: Webhook::class,
            actual: new Webhook(
                url: 'https://test.resurs.com/docs',
                authorization: '',
                continueOnNoResponse: false,
                timeout: 0
            )
        );
    }

    /**
     * Validate negative timeout as failed.
     */
    public function testBadTimeout(): void
    {
        self::expectException(exception: IllegalValueException::class);

        new Webhook(
            url: 'https://test.resurs.com/docs',
            authorization: '',
            continueOnNoResponse: false,
            timeout: -1
        );
    }
}

<?php

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Lib\Model\Rco;

use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Exception\Validation\IllegalUrlException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Model\Rco\CreateCheckout\CreateWebhook;

class CreateWebhookTest extends TestCase
{
    /**
     * Validate a bad url as bad.
     */
    public function testBadWebHook(): void
    {
        $this->expectException(exception: IllegalUrlException::class);
        new CreateWebhook(
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
            expected: CreateWebhook::class,
            actual: new CreateWebhook(
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
        $this->expectException(exception: IllegalValueException::class);

        new CreateWebhook(
            url: 'https://test.resurs.com/docs',
            authorization: '',
            continueOnNoResponse: false,
            timeout: -1
        );
    }
}

<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Widget\TestPurchase;

use JsonException;
use ReflectionException;
use Resursbank\Ecom\Exception\ApiException;
use Resursbank\Ecom\Exception\AuthException;
use Resursbank\Ecom\Exception\CacheException;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\CurlException;
use Resursbank\Ecom\Exception\FilesystemException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Model\PaymentMethod;
use Resursbank\Ecom\Lib\Order\PaymentMethod\Type;
use Resursbank\Ecom\Lib\Widget\Widget;
use Resursbank\Ecom\Module\PaymentMethod\Repository;
use Throwable;

/**
 * Test purchase widget.
 */
class Html extends Widget
{
    /** @var string */
    public readonly string $content;

    /**
     * @throws ConfigException
     * @throws FilesystemException
     */
    public function __construct()
    {
        $this->content = $this->render(
            file: $this->getWidgetName() . DIRECTORY_SEPARATOR . 'templates' .
            DIRECTORY_SEPARATOR . 'html.phtml'
        );
    }

    /**
     * Get payment method ID to use for test purchase.
     *
     * @throws ConfigException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ApiException
     * @throws AuthException
     * @throws CacheException
     * @throws CurlException
     * @throws ValidationException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws Throwable
     * @SuppressWarnings(PHPMD.CountInLoopExpression)
     */
    public static function getPaymentMethodId(): string
    {
        $paymentMethods = Repository::getPaymentMethods();

        for ($i = 0; $i < count($paymentMethods); $i++) {
            if (
                $paymentMethods[$i] instanceof PaymentMethod &&
                (
                    $paymentMethods[$i]->type === Type::RESURS_PART_PAYMENT ||
                    $paymentMethods[$i]->type === Type::RESURS_CARD ||
                    $paymentMethods[$i]->type === Type::RESURS_INVOICE
                )
            ) {
                return $paymentMethods[$i]->getId();
            }
        }

        return '';
    }

    /**
     * @inheritDoc
     */
    public function shouldRender(): bool
    {
        return self::getPaymentMethodId() !== '';
    }
}

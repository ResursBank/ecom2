<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Widget\ConsumerCreditWarning;

use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\FilesystemException;
use Resursbank\Ecom\Lib\Locale\Location;
use Resursbank\Ecom\Lib\Model\PaymentMethod;
use Resursbank\Ecom\Lib\Model\PriceSignage\PriceSignage;
use Resursbank\Ecom\Lib\Order\PaymentMethod\Type;
use Resursbank\Ecom\Lib\Widget\Widget;

/**
 * Display warning message widget.
 */
class Html extends Widget
{
    public string $content;

    /**
     * @throws FilesystemException
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function __construct(
        public readonly PriceSignage $priceSignage,
        public readonly PaymentMethod $paymentMethod,
        private readonly bool $visible = true
    ) {
        $this->content = $this->render(
            file: __DIR__ . DIRECTORY_SEPARATOR . 'templates' .
            DIRECTORY_SEPARATOR . 'html.phtml'
        );
    }

    /**
     * Only display the warning if the cost list is not empty and the location is SE.
     */
    public function shouldRender(): bool
    {
        return $this->priceSignage->costList->count() > 0 &&
            Config::getLocation() === Location::SE &&
            $this->paymentMethod->type !== Type::RESURS_INVOICE &&
            $this->visible;
    }
}

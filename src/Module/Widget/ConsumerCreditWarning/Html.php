<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Widget\ConsumerCreditWarning;

use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\FilesystemException;
use Resursbank\Ecom\Lib\Locale\Location;
use Resursbank\Ecom\Lib\Model\PaymentMethod;
use Resursbank\Ecom\Lib\Model\PaymentMethod\Type;
use Resursbank\Ecom\Lib\Widget\Widget;

/**
 * Display warning message widget.
 */
class Html extends Widget
{
    public const CACHE_KEY_PREFIX =
        'resursbank-ecom-widget-consumer-credit-warning-html';

    public string $content;

    /**
     * @throws FilesystemException
     * @throws ConfigException
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function __construct(
        public readonly PaymentMethod $paymentMethod,
        private readonly bool $visible = true
    ) {
        $this->content = $this->render(
            file: $this->getWidgetName() . DIRECTORY_SEPARATOR . 'templates' .
            DIRECTORY_SEPARATOR . 'html.phtml'
        );
    }

    /**
     * @inheritDoc
     *
     * Only display the warning if the cost list is not empty and the location
     * is Sweden.
     */
    public function shouldRender(): bool
    {
        return $this->paymentMethod->priceSignagePossible &&
            Config::getLocation() === Location::SE &&
            $this->paymentMethod->type !== Type::RESURS_INVOICE &&
            $this->visible;
    }
}

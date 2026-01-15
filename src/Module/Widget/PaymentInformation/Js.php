<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Widget\PaymentInformation;

use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\FilesystemException;
use Resursbank\Ecom\Lib\Log\Logger;
use Resursbank\Ecom\Lib\UserSettings\Url;
use Resursbank\Ecom\Lib\Widget\Widget;
use Resursbank\Ecom\Module\UserSettings\Repository;
use Throwable;

/**
 * JavaScript to reload the payment information widget on demand.
 *
 * @SuppressWarnings(PHPMD.ShortClassName)
 */
class Js extends Widget
{
    /** @var string */
    public readonly string $content;

    /**
     * @param string $amountElement DOM path to amount element.
     * @param array $observableElements List of DOM paths to trigger reload on.
     * @param bool $automatic Whether to initiate JS automatically.
     * @throws FilesystemException
     * @throws ConfigException
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function __construct(
        public readonly string $amountElement,
        public readonly array $observableElements,
        public readonly bool $automatic = true
    ) {
        $this->content = $this->render(
            file: $this->getWidgetName() . '/templates/js.js.phtml'
        );
    }

    public function getUrl(): string
    {
        try {
            return Repository::getUrl(url: Url::RELOAD_PAYMENT_INFORMATION_URL);
        } catch (Throwable $error) {
            Logger::error(message: $error);
        }

        return '';
    }
}

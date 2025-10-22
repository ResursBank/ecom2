<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Widget\PartPayment;

use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Lib\UserSettings\Field;
use Resursbank\Ecom\Lib\Widget\Widget;
use Resursbank\Ecom\Module\UserSettings\Repository as UserSettingsRepository;
use Resursbank\Woocommerce\Util\Log;
use Throwable;

/**
 * Part payment CSS widget.
 */
class Css extends Widget
{
    public const CACHE_KEY_PREFIX =
        'resursbank-ecom-widget-part-payment-css';

    /** @var string */
    public readonly string $content;

    /**
     * @throws ConfigException
     */
    public function __construct()
    {
        $this->content = $this->renderStatic(
            file: $this->getWidgetName() . DIRECTORY_SEPARATOR . 'templates' .
            DIRECTORY_SEPARATOR . 'css.css'
        );
    }

    /**
     * Only render if the part payment feature is enabled.
     *
     * Note that the JS & HTML counterparts also require a payment method, but
     * since that can be directly supplied to them and not this class, we omit
     * this condition here.
     *
     * This should not matter, since all we are rendering is CSS. If it can be
     * fixed in the future, this class should use the same conditions as the JS
     * & HTML counterparts though.
     */
    public function shouldRender(): bool
    {
        try {
            return UserSettingsRepository::isEnabled(
                field: Field::PART_PAYMENT_ENABLED
            );
        } catch (Throwable $error) {
            Log::error(error: $error);
        }

        return false;
    }
}

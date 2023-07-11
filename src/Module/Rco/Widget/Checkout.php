<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Rco\Widget;

use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\FilesystemException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Api\Rco;
use Resursbank\Ecom\Lib\Locale\Rco\Locale;
use Resursbank\Ecom\Lib\Widget\Widget;
use Resursbank\Ecom\Module\Rco\Widget\Checkout\Style;

/**
 * Defines the RCO+ widget
 */
class Checkout extends Widget
{
    private Rco $rco;

    /**
     * @param string $checkoutId Checkout ID
     * @param Locale|null $locale Defaults to locale set in Checkout
     * @param bool $disabled Initialize Checkout with controls disabled
     * @param bool $collapseCart Initialize Checkout with cart hidden
     * @param Style|null $style Styling options
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function __construct(
        public readonly string $checkoutId,
        public readonly ?Locale $locale = null,
        public readonly bool $disabled = false,
        public readonly bool $collapseCart = false,
        public readonly ?Style $style = null
    ) {
        $this->rco = new Rco();
    }

    /**
     * Fetch URL to RCO script at Resurs.
     *
     * @throws ConfigException
     * @throws ValidationException
     * @throws EmptyValueException
     */
    public function getScriptUrl(): string
    {
        return $this->rco->getUrl(route: 'wc/rco.js');
    }

    /**
     * Render out the rco-checkout element.
     *
     * @throws FilesystemException
     */
    public function getBodyElement(): string
    {
        return $this->render(file: __DIR__ . '/checkout.phtml');
    }

    /**
     * Render out the script and styling elements.
     *
     * @throws FilesystemException
     */
    public function getHeaderScript(): string
    {
        return $this->render(file: __DIR__ . '/checkout-script.phtml');
    }

    /**
     * Get a pre-rendered set of styling option strings.
     *
     * @return array
     */
    public function getStyleOptions(): array
    {
        $options = [];

        if (!$this->style) {
            return $options;
        }

        foreach (get_object_vars(object: $this->style) as $key => $value) {
            if (!$value) {
                continue;
            }

            $name = '--rco-' . strtolower(string: (string)preg_replace(
                pattern: '/(?<!^)[A-Z]/',
                replacement: '-$0',
                subject: $key
            ));
            $options[] = $name . ': ' . $value . ';';
        }

        return $options;
    }

    /**
     * Get the rco-checkout element's options as a string.
     */
    public function getOptions(): string
    {
        $output = '';

        if ($this->locale) {
            $output .= ' locale="' . $this->locale->value . '"';
        }

        if ($this->collapseCart) {
            $output .= ' collapseCart';
        }

        if ($this->disabled) {
            $output .= ' disabled';
        }

        return $output;
    }
}

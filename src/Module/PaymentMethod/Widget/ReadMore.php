<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\PaymentMethod\Widget;

use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\FilesystemException;
use Resursbank\Ecom\Lib\Locale\Dictionary;
use Resursbank\Ecom\Lib\Locale\Locale;
use Resursbank\Ecom\Lib\Widget\Widget;
use Resursbank\Ecom\Module\PaymentMethod\Models\PaymentMethod;
use function is_string;

/**
 * Read more widget.
 */
class ReadMore extends Widget
{
    /**
     * @var string
     */
    public string $url = '';

    /**
     * @var string
     */
    public string $content = '';

    /**
     * @var Dictionary
     */
    public Dictionary $label;

    /**
     * @param PaymentMethod $paymentMethod
     * @param float $amount
     * @throws FilesystemException
     */
    public function __construct(
        public readonly PaymentMethod $paymentMethod,
        public readonly float $amount
    ) {
        foreach ($this->paymentMethod->legalLinks as $link) {
            if ($link->type === 'PRICE_INFO') {
                $this->url = $link->url;
            }
        }

        $this->label = new Dictionary(
            en: 'Read more',
            sv: 'Läs mer',
        );

        $this->content = $this->render(file: __DIR__ . '/read-more.phtml');
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        $result = $this->label->{Config::$instance->locale->name};

        if (!is_string(value: $result)) {
            $result = 'Read more';
        }

        return $result;
    }
}

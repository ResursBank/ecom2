<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\PaymentMethod\Widget\Logo;

use Resursbank\Ecom\Lib\Model\PaymentMethod;
use Resursbank\Ecom\Lib\Order\PaymentMethod\Type;
use Resursbank\Ecom\Lib\Widget\Widget as Base;

class Widget extends Base
{
    public string $file;
    public string $html;
    public string $css;

    public function __construct(
        PaymentMethod $paymentMethod
    ) {
        $this->file = match($paymentMethod->type) {
            Type::SWISH => 'swish.png',
            Type::DEBIT_CARD, Type::CREDIT_CARD => 'card.svg',
            Type::INTERNET => 'trustly.svg',
            default => 'resurs.png',
        };

        $this->html = $this->render(__DIR__ . '/html.phtml');
    }

    public function getLogo(bool $inclImgTag = true): string
    {
        $filePath = __DIR__ . '/img/' . $this->file;
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);

        return match($extension) {
            'svg' => $this->getSvgContent($filePath),
            'png' => $this->getPngBase64($filePath, $inclImgTag),
            default => '',
        };
    }

    /**
     * Returns the filename without extension.
     */
    public function getIdentifier(): string
    {
        return pathinfo($this->file, PATHINFO_FILENAME);
    }

    private function getSvgContent(string $filePath): string
    {
        return file_get_contents($filePath);
    }

    private function getPngBase64(string $filePath, bool $inclImgTag = true): string
    {
        $imageData = file_get_contents($filePath);
        $base64 = base64_encode($imageData);
        $result = $inclImgTag ? '<img src="' : '';
        $result .= 'data:image/png;base64,' . $base64;

        return $result . ($inclImgTag ? '" />' : '');
    }
}

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
use RuntimeException;

class Widget extends Base
{
    public string $file;

    public string $html;

    public string $css;

    public function __construct(
        PaymentMethod $paymentMethod
    ) {
        $this->file = match ($paymentMethod->type) {
            Type::SWISH => 'swish.png',
            Type::DEBIT_CARD, Type::CREDIT_CARD => 'card.svg',
            Type::INTERNET => 'trustly.svg',
            default => 'resurs.png',
        };

        $this->html = $this->render(file: __DIR__ . '/html.phtml');
    }

    /**
     * @suppressWarnings(BooleanArgumentFlag)
     */
    public function getLogo(bool $inclImgTag = true): string
    {
        $filePath = __DIR__ . '/img/' . $this->file;
        $extension = pathinfo(path: $filePath, flags: PATHINFO_EXTENSION);

        return match ($extension) {
            'svg' => $this->getSvgContent(filePath: $filePath),
            'png' => $this->getPngBase64(
                filePath: $filePath,
                inclImgTag: $inclImgTag
            ),
            default => '',
        };
    }

    /**
     * Returns the filename without extension.
     */
    public function getIdentifier(): string
    {
        return pathinfo(path: $this->file, flags: PATHINFO_FILENAME);
    }

    private function getSvgContent(string $filePath): string
    {
        $content = file_get_contents(filename: $filePath);

        if ($content === false) {
            throw new RuntimeException(
                message: "Failed to read SVG file at $filePath."
            );
        }

        return $content;
    }

    /**
     * @suppressWarnings(BooleanArgumentFlag)
     */
    private function getPngBase64(string $filePath, bool $inclImgTag = true): string
    {
        $imageData = file_get_contents(filename: $filePath);

        if ($imageData === false) {
            throw new RuntimeException(
                message: "Failed to read PNG file at $filePath."
            );
        }

        $base64 = base64_encode(string: $imageData);
        $result = $inclImgTag ? '<img src="' : '';
        $result .= 'data:image/png;base64,' . $base64;

        return $result . ($inclImgTag ? '" />' : '');
    }
}

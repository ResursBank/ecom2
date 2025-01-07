<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\PaymentMethod\Widget\CompanyCheckout;

use Resursbank\Ecom\Lib\Model\PaymentMethod;
use Resursbank\Ecom\Lib\Widget\Widget as Base;
use RuntimeException;

/**
 * Company Checkout Widget.
 *
 * @noinspection PhpUnusedParameterInspection
 */
class Widget extends Base
{
    public string $html;

    public string $js;

    public string $file;

    /**
     * @noinspection PhpUnusedParameterInspection
     */
    public function __construct(
        PaymentMethod $paymentMethod
    ) {
        $this->html = $this->render(file: __DIR__ . '/widget.html.phtml');
        $this->js = $this->render(file: __DIR__ . '/widget.js.phtml');
    }

    public function getLogo(): string
    {
        $filePath = __DIR__ . '/img/' . $this->file;
        $extension = pathinfo(path: $filePath, flags: PATHINFO_EXTENSION);

        return match ($extension) {
            'svg' => $this->getSvgContent(filePath: $filePath),
            'png' => $this->getPngBase64(filePath: $filePath),
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

    private function getPngBase64(string $filePath): string
    {
        $imageData = file_get_contents(filename: $filePath);

        if ($imageData === false) {
            throw new RuntimeException(
                message: "Failed to read PNG file at $filePath."
            );
        }

        $base64 = base64_encode(string: $imageData);
        return '<img src="data:image/png;base64,' . $base64 . '" />';
    }
}

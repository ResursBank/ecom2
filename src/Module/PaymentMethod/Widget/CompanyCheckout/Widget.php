<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\PaymentMethod\Widget\CompanyCheckout;

use Resursbank\Ecom\Lib\Model\PaymentMethod;
use Resursbank\Ecom\Lib\Order\PaymentMethod\Type;
use Resursbank\Ecom\Lib\Widget\Widget as Base;

class Widget extends Base
{
    public function __construct(
        PaymentMethod $paymentMethod
    ) {
        $this->html = $this->render(__DIR__ . '/widget.html.phtml');
        $this->js = $this->render(__DIR__ . '/widget.js.phtml');
    }

    public function getLogo(): string
    {
        $filePath = __DIR__ . '/img/' . $this->file;
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);

        return match($extension) {
            'svg' => $this->getSvgContent($filePath),
            'png' => $this->getPngBase64($filePath),
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

    private function getPngBase64(string $filePath): string
    {
        $imageData = file_get_contents($filePath);
        $base64 = base64_encode($imageData);
        return '<img src="data:image/png;base64,' . $base64 . '" />';
    }
}

<?php

/**
* Copyright © Resurs Bank AB. All rights reserved.
* See LICENSE for license details.
*/

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Widget\Logo;

use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\FilesystemException;
use Resursbank\Ecom\Lib\Model\PaymentMethod;
use Resursbank\Ecom\Lib\Order\PaymentMethod\Type;
use Resursbank\Ecom\Lib\Widget\Widget;
use RuntimeException;

/**
* Render logotype by base64 encoding the image file.
*
* This widget only supports PNG files.
*/
class Html extends Widget
{
    public const CACHE_KEY_PREFIX = 'resursbank-ecom-widget-logo-html';

    public string $file;

    public string $content;

    /**
     * @throws FilesystemException
     * @throws ConfigException
     */
    public function __construct(
        PaymentMethod $paymentMethod
    ) {
        $this->file = match ($paymentMethod->type) {
            Type::SWISH => 'swish.png',
            Type::DEBIT_CARD, Type::CREDIT_CARD => 'card.png',
            Type::INTERNET => 'trustly.png',
            default => 'resurs.png'
        };

        $this->content = $this->render(
            file: __DIR__ . DIRECTORY_SEPARATOR . 'templates' .
            DIRECTORY_SEPARATOR . 'html.phtml'
        );
    }

    /**
     * @suppressWarnings(BooleanArgumentFlag)
     */
    public function getLogo(bool $inclImgTag = true): string
    {
        $filePath = __DIR__ . '/img/' . $this->file;
        $extension = pathinfo(path: $filePath, flags: PATHINFO_EXTENSION);

        return match ($extension) {
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

    /**
     * @suppressWarnings(BooleanArgumentFlag)
     */
    public function getPngBase64(string $filePath, bool $inclImgTag = true): string
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

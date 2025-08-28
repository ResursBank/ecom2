<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Widget\PaymentMethod;

use Resursbank\Ecom\Exception\FilesystemException;
use Resursbank\Ecom\Lib\Model\Interface\PaymentMethodCollection;
use Resursbank\Ecom\Lib\Widget\Widget;

/**
 * Payment methods table widget.
 */
class Html extends Widget
{
    /** @var string */
    public readonly string $content;

    /**
     * @throws FilesystemException
     */
    public function __construct(
        public readonly PaymentMethodCollection $paymentMethods
    ) {
        $this->content = $this->render(
            file: __DIR__ . DIRECTORY_SEPARATOR . 'templates' .
            DIRECTORY_SEPARATOR . 'html.phtml'
        );
    }
}

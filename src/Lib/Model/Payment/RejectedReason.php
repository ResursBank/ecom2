<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Payment;

use Resursbank\Ecom\Lib\Locale\Translator;
use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Model\Payment\Enum\RejectedReasonCategory;
use Throwable;

/**
 * Model used to describe Payment rejection reason.
 */
class RejectedReason extends Model
{
    public function __construct(
        public readonly ?RejectedReasonCategory $category = null
    ) {
    }

    /**
     * Fetches a customer-facing description of the rejection reason.
     */
    public function getFriendlyDescription(): string
    {
        return $this->getTranslation(suffix: '');
    }

    /**
     * Fetches an admin-facing description of the rejection reason.
     *
     * Used for order notes and backend displays where a more technical
     * description is appropriate.
     */
    public function getAdminDescription(): string
    {
        return $this->getTranslation(suffix: '-admin');
    }

    /**
     * Translate the rejection reason category with an optional suffix.
     */
    private function getTranslation(string $suffix): string
    {
        if ($this->category !== null) {
            try {
                return Translator::translate(
                    phraseId: str_replace(
                        search: '_',
                        replace: '-',
                        subject: strtolower(string: $this->category->value)
                    ) . $suffix,
                    translationFile: __DIR__ .
                    '/RejectedReason/Resources/translations.json'
                );
            } catch (Throwable) {
                return $this->category->value;
            }
        }

        return '';
    }
}

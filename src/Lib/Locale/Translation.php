<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Locale;

use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Validation\StringValidation;

/**
 * Translated phrase. The phrase has to be translated to the languages listed
 * in the constructor, and cannot be an empty string. Base language is english.
 */
class Translation extends Model
{
    /** @var string Translation string for Finnish (fi_FI).  */
    public string $fi;
    /** @var string Translation string for Norwegian (Variants no_NO, nb_NO, nn_NO). */
    public string $no;
    /** @var string Translation string for Danish (da_DK). */
    public string $da;

    /**
     * Translations for multiple languages, with failover to english.
     * @throws EmptyValueException
     */
    public function __construct(
        public readonly string $sv,
        public readonly string $en,
        string $fi = '',
        string $no = '',
        string $da = '',
        private readonly StringValidation $stringValidation = new StringValidation()
    ) {
        if ($fi === '') {
            $fi = $en;
        }

        if ($no === '') {
            $no = $en;
        }

        if ($da === '') {
            $da = $en;
        }

        $this->fi = $fi;
        $this->no = $no;
        $this->da = $da;

        $this->validateTranslation(value: $this->sv);
        $this->validateTranslation(value: $this->en);
        $this->validateTranslation(value: $this->fi);
        $this->validateTranslation(value: $this->no);
        $this->validateTranslation(value: $this->da);
    }

    /**
     * @throws EmptyValueException
     */
    public function validateTranslation(string $value): void
    {
        $this->stringValidation->notEmpty(value: $value);
    }
}

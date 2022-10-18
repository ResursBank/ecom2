<?php /** @noinspection PhpCSValidationInspection */

/**
* Copyright © Resurs Bank AB. All rights reserved.
* See LICENSE for license details.
*/

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\PaymentMethod\ApplicationFormSpecResponse\ApplicationFormSpecElementResponse;

enum Type: string
{
    case TEXT = 'TEXT';
    case NUMBER = 'NUMBER';
    case LIST = 'LIST';
    case CHECKBOX = 'CHECKBOX';
    case TOGGLE = 'TOGGLE';
    case HEADING = 'HEADING';
}

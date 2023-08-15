<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco\PaymentMethod;

/**
 * Payment method type.
 */
enum Type: string
{
    case GENERIC = 'GENERIC';
    case RESURS_INVOICE = 'RESURS_INVOICE';
    case RESURS_PART_PAYMENT = 'RESURS_PART_PAYMENT';
    case RESURS_CARD = 'RESURS_CARD';
    case RESURS_REVOLVING_CREDIT = 'RESURS_REVOLVING_CREDIT';
    case NETS = 'NETS';
    case D2I = 'D2I';
    case SWISH = 'SWISH';
    case TRUSTLY = 'TRUSTLY';
    case ZERO = 'ZERO';
    case VIPPS = 'VIPPS';
}

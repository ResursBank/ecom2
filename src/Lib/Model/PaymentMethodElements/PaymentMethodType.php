<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\PaymentMethodElements;

/**
 * Defines available method types available in Payment Method Elements.
 */
enum PaymentMethodType: string
{
    case GENERIC = 'GENERIC';
    case RESURS_INVOICE = 'RESURS_INVOICE';
    case RESURS_PART_PAYMENT_SELECT_LATER = 'RESURS_PART_PAYMENT_SELECT_LATER';
    case RESURS_PART_PAYMENT_SELECT_NOW = 'RESURS_PART_PAYMENT_SELECT_NOW';
    case RESURS_CARD = 'RESURS_CARD';
    case RESURS_REVOLVING_CREDIT = 'RESURS_REVOLVING_CREDIT';
    case RESURS_NEW_REVOLVING_CREDIT = 'RESURS_NEW_REVOLVING_CREDIT';
    case NETS = 'NETS';
    case D2I = 'D2I';
    case SWISH = 'SWISH';
    case TRUSTLY = 'TRUSTLY';
    case ZERO = 'ZERO';
    case VIPPS = 'VIPPS';
    case MOBILEPAY = 'MOBILEPAY';
}

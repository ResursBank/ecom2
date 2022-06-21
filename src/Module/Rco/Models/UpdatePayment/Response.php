<?php

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Rco\Models\UpdatePayment;

use Resursbank\Ecom\Lib\Model\Model;

class Response extends Model
{
    public function __construct(
        private string $message
    ) {
        parent::__construct();
    }
}

<?php

declare(strict_types=1);

namespace Resursbank\Ecom\Module\PaymentMethod\Models\PaymentMethod;

use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Validation\ArrayValidation;
use Resursbank\Ecom\Module\PaymentMethod\Enum\Status\DisabledReasons;
use function is_string;

/**
 * Defines the status property of a payment method and associated values.
 */
class Status extends Model
{
    /**
     * @param bool $disabled
     * @param bool $requireLimitRaise
     * @param array $disabledReasons
     * @param ArrayValidation $arrayValidation
     * @throws IllegalTypeException
     * @throws IllegalValueException
     */
    public function __construct(
        public readonly bool $disabled,
        public readonly bool $requireLimitRaise,
        public readonly array $disabledReasons,
        private readonly ArrayValidation $arrayValidation = new ArrayValidation()
    ) {
        $this->validateDisabledReasons();
    }

    /**
     * @return void
     * @throws IllegalTypeException
     * @throws IllegalValueException
     */
    private function validateDisabledReasons(): void
    {
        if (count($this->disabledReasons)) {
            $this->arrayValidation->isSequential(data: $this->disabledReasons);

            foreach ($this->disabledReasons as $item) {
                if (!is_string(value: $item)) {
                    throw new IllegalTypeException(
                        message: 'Disabled reasons must be a list of strings.'
                    );
                }

                /** @noinspection PhpExpressionResultUnusedInspection */
                DisabledReasons::from(value: $item);
            }
        }
    }
}

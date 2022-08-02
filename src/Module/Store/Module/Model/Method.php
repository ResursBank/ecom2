<?php

declare(strict_types=1);

namespace Resursbank\Ecom\Module\PaymentMethod\Model;

use Exception;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalCharsetException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Validation\ArrayValidation;
use Resursbank\Ecom\Lib\Validation\StringValidation;
use Resursbank\Ecom\Module\PaymentMethod\Model\Item\Status;

use function is_string;

/**
 * Defines an API request to collect a list of payment methods.
 *
 * NOTE: All Exceptions from namespace Validation extends ValidationException.
 */
class Method
{
    /**
     * @param string $id | API identifier.
     * @param string $customerType | 'NATURAL' (private), 'LEGAL' (company), '' (all)
     * @param int $displayOrder | Listing order.
     * @param string $description | Title.
     * @param string $validFrom | Available from this date.
     * @param string $validTo | Available until.
     * @param array $supportedActions | List of available method actions.
     * @param float|int $minPurchaseLimit | Min purchase amount.
     * @param float|int $maxPurchaseLimit | Max purchase amount.
     * @param float|int $minApplicationLimit | Min application value.
     * @param float|int $maxApplicationLimit | Max application value.
     * @param string $type | Method subset (category).
     * @param Status $status | Describes if and why a method is disabled.
     * @param StringValidation $stringValidation
     * @param ArrayValidation $arrayValidation
     * @throws EmptyValueException
     * @throws IllegalCharsetException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @todo Confirm data types of min/maxPurchase/ApplicationLimit properties.
     * @todo validFom / validTo converts to DateTime(), consider adding method to extract them as such, or a method
     * @todo to confirm validation between these dates.
     */
    public function __construct(
        public readonly string $id,
        public readonly string $customerType,
        public readonly int $displayOrder,
        public readonly string $description,
        public readonly string $validFrom,
        public readonly string $validTo,
        public readonly array $supportedActions,
        public readonly float|int $minPurchaseLimit,
        public readonly float|int $maxPurchaseLimit,
        public readonly float|int $minApplicationLimit,
        public readonly float|int $maxApplicationLimit,
        public readonly string $type,
        public readonly Status $status,
        private readonly StringValidation $stringValidation = new StringValidation(),
        private readonly ArrayValidation $arrayValidation = new ArrayValidation(),
    ) {
        $this->validateId();
        $this->validateCustomerType();
        $this->validateDescription();
        $this->validateValidFrom();
        $this->validateValidTo();
        $this->validateSupportedActions();
        $this->validateType();
    }

    /**
     * @throws IllegalCharsetException
     * @throws EmptyValueException
     */
    private function validateId(): void
    {
        $this->stringValidation->notEmpty(value: $this->id);
        $this->stringValidation->matchRegex(
            value: $this->id,
            pattern: '/^[\da-z\-]+$/'
        );
    }

    /**
     * @throws IllegalValueException
     */
    private function validateCustomerType(): void
    {
        $this->stringValidation->oneOf(
            value: $this->customerType,
            set: ['NATURAL', 'LEGAL', '']
        );
    }

    /**
     * @throws EmptyValueException
     * @todo Add charset validation.
     */
    private function validateDescription(): void
    {
        $this->stringValidation->notEmpty(value: $this->description);
    }

    /**
     * @throws EmptyValueException
     * @throws IllegalValueException
     * @throws Exception
     */
    private function validateValidFrom(): void
    {
        $this->stringValidation->notEmpty(value: $this->validFrom);
        $this->stringValidation->isDate(value: $this->validFrom);
    }

    /**
     * @throws EmptyValueException
     * @throws IllegalValueException
     * @throws Exception
     */
    private function validateValidTo(): void
    {
        $this->stringValidation->notEmpty(value: $this->validTo);
        $this->stringValidation->isDate(value: $this->validTo);
    }

    /**
     * @throws IllegalCharsetException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws EmptyValueException
     * @todo We should investigate if there is a specific set of actions we can validate against.
     * @todo Confirm charset validation.
     */
    private function validateSupportedActions(): void
    {
        if (count($this->supportedActions)) {
            $this->arrayValidation->isSequential(data: $this->supportedActions);

            // Values must be non-empty strings consisting of A-Z and underscore.
            foreach ($this->supportedActions as $item) {
                if (!is_string(value: $item)) {
                    throw new IllegalTypeException(
                        message: 'Array may only consist of strings.'
                    );
                }

                $this->stringValidation->notEmpty(value: $item);
                $this->stringValidation->matchRegex(
                    value: $item,
                    pattern: '/^[A-Z_]+$/'
                );
            }
        }
    }

    /**
     * @throws EmptyValueException
     * @throws IllegalCharsetException
     * @todo Confirm charset validation.
     */
    private function validateType(): void
    {
        $this->stringValidation->notEmpty(value: $this->type);
        $this->stringValidation->matchRegex(
            value: $this->type,
            pattern: '/^[A-Z_]+$/'
        );
    }
}

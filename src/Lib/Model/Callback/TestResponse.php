<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Callback;

use JsonException;
use ReflectionException;
use Resursbank\Ecom\Exception\AttributeCombinationException;
use Resursbank\Ecom\Lib\Attribute\Validation\IntValue;
use Resursbank\Ecom\Lib\Model\Callback\Enum\TestStatus;
use Resursbank\Ecom\Lib\Model\Model;

/**
 * Implementation of response from triggering test callback.
 */
class TestResponse extends Model
{
    /**
     * @param TestStatus $status Test callback status
     * @param int $code HTTP code.
     * @throws JsonException
     * @throws ReflectionException
     * @throws AttributeCombinationException
     */
    public function __construct(
        public readonly TestStatus $status,
        #[IntValue(min:100, max:599)] public readonly int $code
    ) {
        parent::__construct();
    }

    /**
     * Convert response to JSON format for JavaScript consumption.
     *
     * @return string JSON encoded response
     * @throws JsonException If JSON encoding fails
     */
    public function toJson(): string
    {
        try {
            $data = [
                'status' => $this->status->value,
                'code' => $this->code,
                'success' => $this->status === TestStatus::OK,
            ];

            // Add error message if status is not OK
            if ($this->status !== TestStatus::OK) {
                $data['error'] = $this->getErrorMessage();
            }

            return json_encode(
                value: $data,
                flags: JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT
            );
        } catch (JsonException $e) {
            // Fallback error response if JSON encoding fails
            return json_encode(
                value: [
                    'status' => 'ERROR',
                    'code' => 500,
                    'success' => false,
                    'error' => 'Failed to encode response: ' . $e->getMessage()
                ],
                flags: JSON_THROW_ON_ERROR
            );
        }
    }

    /**
     * Get human-readable error message based on status.
     *
     * @return string Error message
     */
    private function getErrorMessage(): string
    {
        return match ($this->status) {
            TestStatus::FAILED => 'Callback test failed.',
            TestStatus::INVALID_URL => 'Invalid callback URL provided.',
            TestStatus::ERROR => 'An error occurred during callback test.',
            TestStatus::TIMEOUT => 'Callback test request timed out.',
            TestStatus::RATE_LIMITED => 'Rate limit exceeded. Please try again later.',
            TestStatus::OK => '',
        };
    }
}

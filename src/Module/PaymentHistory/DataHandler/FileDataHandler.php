<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\PaymentHistory\DataHandler;

use ReflectionException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Model\PaymentHistory\Entry;
use Resursbank\Ecom\Lib\Model\PaymentHistory\EntryCollection;
use Resursbank\Ecom\Lib\Utilities\DataConverter;

/**
 * Class to store and read payment history data from filesystem.
 */
class FileDataHandler implements DataHandlerInterface
{
    public function __construct(private readonly string $file)
    {
    }

    /**
     * @inheritDoc
     * @throws IllegalTypeException
     * @throws ReflectionException
     * @throws IllegalValueException
     */
    public function write(Entry $entry): void
    {
        $currentCollection = $this->getList(paymentId: $entry->paymentId);
        $currentCollection->offsetSet(offset: null, value: $entry);

        $jsonData = json_encode(
            value: $currentCollection->toArray(),
            flags: JSON_PRETTY_PRINT
        );

        file_put_contents(filename: $this->file, data: $jsonData);
    }

    /**
     * @inheritDoc
     * @throws IllegalTypeException
     * @throws ReflectionException
     * @throws IllegalValueException
     */
    public function getList(string $paymentId): ?EntryCollection
    {
        $result = $this->filterListContent(
            content: $this->getFileContent(),
            paymentId: $paymentId
        );

        $collection = !empty($result) ?
            DataConverter::arrayToCollection(data: $result, type: Entry::class) :
            null;

        if (!$collection instanceof EntryCollection) {
            throw new IllegalTypeException(
                message: 'The conversion did not result in an EntryCollection instance.'
            );
        }

        return $collection;
    }

    /**
     * Filter Entry data from array based on supplied paymentId.
     */
    private function filterListContent(
        array $content,
        string $paymentId
    ): array {
        foreach ($content as $key => $entry) {
            if (!isset($entry->paymentId) || $entry->paymentId === $paymentId) {
                continue;
            }

            unset($content[$key]);
        }

        return $content;
    }

    /**
     * Resolve file content as array of stdClass instances.
     */
    private function getFileContent(): array
    {
        if (!file_exists(filename: $this->file)) {
            return [];
        }

        $result = [];
        $content = file_get_contents(filename: $this->file);

        if ($content !== false) {
            $decodedData = json_decode(json: $content);

            if (
                is_array(value: $decodedData) &&
                json_last_error() === JSON_ERROR_NONE
            ) {
                $result = $decodedData;
            }
        }

        return $result;
    }
}

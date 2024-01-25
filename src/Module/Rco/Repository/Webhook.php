<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Rco\Repository;

use JsonException;
use ReflectionException;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\FilesystemException;
use Resursbank\Ecom\Exception\TranslationException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Exception\WebhookException;
use Resursbank\Ecom\Lib\Locale\Translator;
use Resursbank\Ecom\Lib\Model\Rco\Checkout;
use Resursbank\Ecom\Lib\Utilities\DataConverter;
use Throwable;

use function is_object;

/**
 * Webhook-related methods for RCO+.
 */
class Webhook
{
    /**
     * Convert php://input stream data to a CheckoutDto instance.
     *
     * @throws ConfigException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws WebhookException
     * @throws FilesystemException
     * @throws TranslationException
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public static function getRequestData(?string $post = null): Checkout
    {
        /** @noinspection BadExceptionsProcessingInspection */
        try {
            $data = $post ?? file_get_contents(filename: 'php://input');

            if (!$data) {
                throw new WebhookException(message: 'Missing data.');
            }

            $data = json_decode(
                json: (string)$post,
                associative: false,
                depth: 512,
                flags: JSON_THROW_ON_ERROR
            );

            if (!is_object(value: $data)) {
                throw new WebhookException(
                    message: 'Failed converting submitted data into an object.'
                );
            }

            $result = DataConverter::stdClassToType(
                object: $data,
                type: Checkout::class
            );

            if (!$result instanceof Checkout) {
                throw new IllegalValueException(
                    message: 'Received data could not be converted to CheckoutDto instance.'
                );
            }
        } catch (Throwable $error) {
            Config::getLogger()->debug(message: $error);

            throw new WebhookException(
                message: Translator::translate(
                    phraseId: 'invalid-webhook-data'
                )
            );
        }

        return $result;
    }
}

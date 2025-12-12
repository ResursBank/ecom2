<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\UserSettings;

use BackedEnum;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionParameter;
use Resursbank\Ecom\Exception\FilesystemException;
use Resursbank\Ecom\Exception\TranslationException;
use Resursbank\Ecom\Exception\UserSettingsException;
use Resursbank\Ecom\Exception\Validation\IllegalUrlException;
use Resursbank\Ecom\Lib\Api\Environment;
use Resursbank\Ecom\Lib\Attribute\Validation\StringIsUrl;
use Resursbank\Ecom\Lib\Cache\AbstractCache;
use Resursbank\Ecom\Lib\Locale\Location;
use Resursbank\Ecom\Lib\Locale\Translator;
use Resursbank\Ecom\Lib\Model\PaymentMethod;
use Resursbank\Ecom\Lib\UserSettings\Url;
use Resursbank\Ecom\Module\PaymentMethod\Repository as PaymentMethodRepository;
use Resursbank\Ecom\Module\Store\Repository as StoreRepository;
use Throwable;
use JsonException;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Lib\Log\Traits\ExceptionLog;
use Resursbank\Ecom\Lib\UserSettings\Field;
use Resursbank\Ecom\Lib\Model\UserSettings;
use Resursbank\Ecom\Lib\Repository\Cache;
use ValueError;

class Repository
{
    use ExceptionLog;

    private static ?array $userSettingsParams = null;

    /**
     * NOTE: Exceptions cannot be logged here, because logging will check if
     * logging is enabled, what log level is configured etc. Which means this
     * can lead to circular calls if the exception was caused by a config
     * issue.
     *
     * @return UserSettings
     * @throws UserSettingsException
     * @todo Currently, we cannot log from this function since logging will collect settings in order to check log level. This needs solving so we can log directly from the catch block in this method I think. I'm not sure how now, we should consider it.
     */
    public static function getSettings(): UserSettings
    {
        try {
            // Read from cache, based on metadata to keep unique by store etc.
            $cache = self::getCache();

            $settings = $cache->read();

            if ($settings instanceof UserSettings) {
                return $settings;
            }

            // Read using settings integration (from database most likely).
            $fields = Field::cases();

            $args = [];
            foreach ($fields as $field) {
                $value = self::getValue(field: $field);

                $args[$field->value] = match ($field) {
                    // Special case: Store ID must always be string|null in
                    // UserSettings. Since getValue can return other types
                    // (e.g. int), we need to cast it here to be sure.
                    Field::STORE_ID => $value === null ? null : (string)$value,
                    default => $value,
                };
            }

            $settings = new UserSettings(...$args);
            $cache->write(data: $settings);
        } catch (Throwable $e) {
            throw new UserSettingsException(
                message: 'Failed to get user settings',
                code: 0,
                previous: $e
            );
        }

        return $settings;
    }

    /**
     * Get specific user settings value, cast to correct type.
     *
     * This method will attempt to resolve default value as well using
     * customized methods on the reader instance, if supplied by the
     * implementation. This is to support cases with complex business logic
     * to resolve default value for various settings.
     *
     * NOTE: This method will read values directly from the integration
     * without any caching. The getSettings method should be preferred
     * whenever possible for this reason.
     *
     * @throws ConfigException
     */
    public static function getValue(Field $field)
    {
        // Get config reader instance.
        $reader = Config::getSettingsReader();

        // Fetch raw value using reader, this is the data read directly from
        // the integration database most likely.
        $value = $reader->read(field: $field);;

        // Name of the settings parameter in the constructor of UserSettings.
        $fieldName = $field->value;

        // Get list of parameters in the UserSettings constructor.
        $param = self::getUserSettingsParam(search: $fieldName);

        // If the value is null, or an empty string, assume no value existed in
        // the database, and attempt to resolve a default value instead.
        if ($value === null || $value === '') {
            $value = self::getDefault(field: $field);
        }

        // If this is the part payment threshold field, use the
        // getPartPaymentThreshold function to either return the configured
        // threshold, or the default threshold based on the API account country.
        if ($field === Field::PART_PAYMENT_THRESHOLD) {
            return self::getPartPaymentThreshold(configured: $value);
        }

        // If the value is null, and the parameter allows null, return null
        // instead of type-casting it.
        if ($value === null && $param->allowsNull()) {
            return null;
        }

        // Type-cast resolved value. When we read directly from a database, for
        // example, everything is a string. We get the expected type from the
        // UserSettings constructor parameter type-hint, and use that metadata
        // to cast the value to the correct type.
        $typeName = $param->getType()->getName();

        // Check if the parameter from UserSettings is an enum, and if so,
        // attempt to resolve the enum value using the "from" method.
        if (
            enum_exists(enum: $typeName) &&
            is_subclass_of(object_or_class: $typeName, class: BackedEnum::class)
        ) {
            // $value can default to an enum case, and if so attempting the
            // ::from call will cause a needless error. If the value is not a
            // backed type (string | int), just return it directly. Ecom's
            // Model validation ensures the value is acceptable already.
            if (!is_string(value: $value) && !is_int(value: $value)) {
                return $value;
            }

            // Parse numeric values to int, otherwise enum::from will fail for
            // int backed enums when the value is a numeric string.
            if (is_numeric(value: $value)) {
                $value = (int)$value;
            }

            try {
                return $typeName::from(value: $value);
            } catch (ValueError) {
                throw new InvalidArgumentException(message: 'Invalid enum value.');
            } catch (Throwable) {
                throw new InvalidArgumentException(message: 'Unexpected error while reading form enum.');
            }
        }

        // Simply type-case to basic types.
        return match ($typeName) {
            'int' => (int)$value,
            'float' => (float)$value,
            'bool' => filter_var(
                value: $value,
                filter: FILTER_VALIDATE_BOOLEAN,
                options: FILTER_NULL_ON_FAILURE
            ),
            'string' => (string)$value,
            default => throw new InvalidArgumentException(
                message: "Cannot cast value for field '$fieldName' to type '$typeName'"
            ),
        };
    }

    /**
     * Resolve configured Client ID based on environment.
     *
     * NOTE: This methods avoids getSettings to prevent circular calls.
     *
     * @return string|null
     * @throws ConfigException
     */
    public static function getClientId(): ?string
    {
        return match (self::getValue(field: Field::ENVIRONMENT)) {
            Environment::PROD => self::getValue(field: Field::CLIENT_ID_PROD),
            Environment::TEST => self::getValue(field: Field::CLIENT_ID_TEST),
        };
    }

    /**
     * Resolve configured Client Secret based on environment.
     *
     *  NOTE: This methods avoids getSettings to prevent circular calls.
     *
     * @return string|null
     * @throws ConfigException
     */
    public static function getClientSecret(): ?string
    {
        return match (self::getValue(field: Field::ENVIRONMENT)) {
            Environment::PROD => self::getValue(field: Field::CLIENT_SECRET_PROD),
            Environment::TEST => self::getValue(field: Field::CLIENT_SECRET_TEST),
        };
    }

    /**
     * Check if user credentials are configured.
     */
    public static function hasUserCredentials(): bool
    {
        try {
            $clientId = (string)self::getClientId();
            $clientSecret = (string)self::getClientSecret();
        } catch (Throwable) {
            return false;
        }

        return $clientId !== '' && $clientSecret !== '';
    }

    /**
     * Shortcut to resolve flag from user settings.
     *
     * @throws ConfigException
     */
    public static function isEnabled(Field $field): bool
    {
        $value = self::getValue(field: $field);

        if (is_bool(value: $value)) {
            return $value;
        }

        throw new InvalidArgumentException(
            message: "Field '$field->value' is not a boolean."
        );
    }

    /**
     * Clear user settings cache.
     */
    public static function clearCache(): void
    {
        self::getCache()->clear();
    }

    /**
     * Resolve default value for UserSettings model property.
     *
     * We either use a custom method on the reader instance, named after the
     * property in the UserSettings model (e.g. getDefaultLogLevel for
     * Field::LOG_LEVEL), or we use the default value defined in the
     * constructor of UserSettings if any.
     *
     * @throws ConfigException
     */
    public static function getDefault(Field $field): mixed
    {
        try {
            $methodName = 'getDefault' . str_replace(
                search: ' ',
                replace: '',
                subject: ucwords(string: str_replace(search: '_', replace: ' ', subject: $field->value))
            );

            $reader = Config::getSettingsReader();

            if (method_exists(object_or_class: $reader, method: $methodName)) {
                return $reader->$methodName();
            }

            return self::getDefaultFromParam(field: $field);
        } catch (Throwable $e) {
            // @todo Logging missing, cause using logging currently required UserSettings
        }

        return null;
    }

    public static function getDefaultFromParam(Field $field): mixed
    {
        try {
            $param = self::getUserSettingsParam(search: $field->value);

            if ($param->isDefaultValueAvailable()) {
                return $param->getDefaultValue();
            }
        } catch (Throwable $e) {
            // @todo Logging missing, cause using logging currently required UserSettings
        }

        return null;
    }

    /**
     * Resolve URL from integration and validates it.
     *
     * @throws ConfigException
     * @throws IllegalUrlException
     * @throws UserSettingsException
     */
    public static function getUrl(Url $url): string
    {
        // Get URL from reader.
        $reader = Config::getSettingsReader();
        $value = $reader->getUrl(url: $url);

        // If there is no URL, raise an error.
        if ($value === null) {
            throw new UserSettingsException(message: "No URL configured for '{$url->name}'");
        }

        // Validate value.
        (new StringIsUrl())->validate(name: $url->name, value: $value);

        return $value;
    }

    /**
     * Get payment method from the API based on the configured ID in settings.
     */
    public static function getPartPaymentMethod() : ?PaymentMethod
    {
        try {
            $id = self::getValue(field: Field::PART_PAYMENT_METHOD_ID);

            if (!self::hasUserCredentials()) {
                return null;
            }

            return PaymentMethodRepository::getById(paymentMethodId: $id);
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * Return the configured value if any, otherwise resolve default value if
     * there is a valid API account configured.
     */
    protected static function getPartPaymentThreshold(?string $configured): ?float
    {
        if (is_numeric(value: $configured)) {
            return (float) $configured;
        }

        // Return default value based on the country tied to the API acc.
        return self::getDefaultPartPaymentThreshold();
    }

    /**
     * Return the default part payment threshold based on the configured API
     * account country.
     */
    public static function getDefaultPartPaymentThreshold(): ?float
    {
        return match (Config::getLocation()) {
            Location::FI => 15.00,
            default => 150.00,
        };
    }

    /**
     * Resolve store id from user settings, fall back to first available if a
     * valid API account configured.
     *
     * @throws UserSettingsException
     */
    public static function getStoreId(): ?string
    {
        try {
            $id = self::getSettings()->storeId;

            // Return configured store id if any, otherwise attempt to fall back
            // to first available store value.
            if ($id !== null) {
                return $id;
            }

            // Cannot resolve store without credentials.
            if (Config::getJwtAuth() === null || !self::hasUserCredentials()) {
                return null;
            }

            // If no store has been configured, use the first available store from
            // the API (if any).
            return StoreRepository::getStores()->getFirst()?->id ?? null;
        } catch (Throwable) {
            throw new UserSettingsException(message: 'Failed to retrieve store id.');
        }
    }

    /**
     * Resolve the ReflectionParameter for a given field name in the
     * UserSettings constructor.
     */
    protected static function getUserSettingsParam(string $search): ReflectionParameter
    {
        if (self::$userSettingsParams === null) {
            $reflection = new ReflectionClass(objectOrClass: UserSettings::class);
            self::$userSettingsParams = $reflection->getConstructor()->getParameters();
        }

        foreach (self::$userSettingsParams as $param) {
            if ($param->getName() === $search) {
                $type = $param->getType();

                if (!$type instanceof ReflectionNamedType) {
                    throw new InvalidArgumentException(message: "Unsupported type for field '$param'");
                }

               return $param;
            }
        }

        throw new InvalidArgumentException(message: "Field '$search' not found in UserSettings constructor");
    }

    /**
     * Get cache key for user settings.
     */
    protected static function getCacheKey(): string
    {
        $metadata = Config::getSettingsMetadata();

        return 'user-settings-' . sha1(string: serialize(value: $metadata));
    }

    /**
     * Get cache instance for user settings.
     */
    protected static function getCache(): Cache
    {
        return new Cache(
            key: self::getCacheKey(),
            model: UserSettings::class,
            ttl: 3600
        );
    }
}

<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom;

use Exception;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Lib\Cache\CacheInterface;
use Resursbank\Ecom\Lib\Cache\None;
use Resursbank\Ecom\Lib\Locale\Language;
use Resursbank\Ecom\Lib\Locale\Location;
use Resursbank\Ecom\Lib\Log\FileLogger;
use Resursbank\Ecom\Lib\Log\LoggerInterface;
use Resursbank\Ecom\Lib\Log\NoneLogger;
use Resursbank\Ecom\Lib\Model\Config\Network;
use Resursbank\Ecom\Lib\Model\Network\Auth\Jwt;
use Resursbank\Ecom\Lib\Model\PaymentHistory\DataHandler\DataHandlerInterface;
use Resursbank\Ecom\Lib\Model\PaymentHistory\DataHandler\VoidDataHandler;
use Resursbank\Ecom\Lib\Model\UserSettings;
use Resursbank\Ecom\Lib\UserSettings\Field;
use Resursbank\Ecom\Module\UserSettings\Repository as UserSettingsRepository;
use Resursbank\Ecom\Lib\Model\UserSettings\Metadata;
use Resursbank\Ecom\Lib\UserSettings\NullReader;
use Resursbank\Ecom\Lib\UserSettings\ReaderInterface;
use Resursbank\Ecom\Module\PaymentMethod\Enum\CurrencyFormat;
use Resursbank\Ecom\Module\Store\Repository;
use Throwable;

use function dirname;

/**
 * API communication object.
 *
 * @SuppressWarnings(PHPMD.ExcessiveParameterList)
 * @SuppressWarnings(PHPMD.LongVariable)
 * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
 * @noinspection PhpClassHasTooManyDeclaredMembersInspection
 */
final class Config
{
    /**
     * NOTE: This is a singleton class. Use Config::setup() to generate an
     * instance, use getter methods to extract properties safely.
     *
     * NOTE: Nullable to allow unsetting configuration.
     */
    private static ?Config $instance = null;

    /**
     * NOTE: By default we only log INFO level messages.
     *
     * @param Language|null $language | Not readonly to allow dynamic assignment
     * based on configured store after initializing the Config instance.
     * @todo Create a null cache driver, so there always is one, returns null always
     * @todo Create a null database driver, so there always is one, returns null always
     */
    public function __construct(
        private LoggerInterface $logger,
        private CacheInterface $cache,
        private ?Jwt $jwtAuth,
        private readonly DataHandlerInterface $paymentHistoryDataHandler,
        private readonly bool $isProduction,
        private ?Language $language,
        private ?Location $location,
        private readonly string $currencySymbol,
        private readonly CurrencyFormat $currencyFormat,
        private readonly Network $network,
        private ?string $storeId,
        private readonly bool $cacheWidgets,
        private readonly ReaderInterface $settingsReader,
        private readonly Metadata $settingsMetadata
    ) {
    }

    /**
     * @noinspection PhpTooManyParametersInspection
     * @todo Consider making userAgent an object instead.
     * @todo Consider moving proxy, proxyType and timeout to a separate object.
     * @throws ConfigException
     */
    public static function setup(
        LoggerInterface $logger = new NoneLogger(),
        CacheInterface $cache = new None(),
        ?Jwt $jwtAuth = null,
        DataHandlerInterface $paymentHistoryDataHandler = new VoidDataHandler(),
        bool $isProduction = false,
        ?Language $language = null,
        Location $location = Location::SE,
        string $currencySymbol = 'kr',
        CurrencyFormat $currencyFormat = CurrencyFormat::SYMBOL_LAST,
        Network $network = new Network(),
        ?string $storeId = null,
        bool $cacheWidgets = false,
        ReaderInterface $settingsReader = new NullReader(),
        Metadata $settingsMetadata = new Metadata(),
    ): void {
        self::$instance = new Config(
            logger: $logger,
            cache: $cache,
            jwtAuth: $jwtAuth,
            paymentHistoryDataHandler: $paymentHistoryDataHandler,
            isProduction: $isProduction,
            language: $language,
            location: $location,
            currencySymbol: $currencySymbol,
            currencyFormat: $currencyFormat,
            network: $network,
            storeId: $storeId,
            cacheWidgets: $cacheWidgets,
            settingsReader: $settingsReader,
            settingsMetadata: $settingsMetadata,
        );

        self::configure();
    }

    /**
     * Populate Ecom instance with data read from integration user settings.
     *
     * Basically, read settings like client id / secret / env etc. from the
     * database (or wherever) the integration stores them, and configure our
     * Ecom instance using them.
     *
     * @todo It needs considering if the getter methods should access the config directly. In some cases this makes sense, but keeping it centralized here means we do not need to access the config layer as often, and some operations like creating the file logger won't need to happen over and over. Separating it to the getters is cleaner, the operations are usually not expensive (especially if cache is enabled, and the ecom instance it also cached). Food for thought, keeping it all here at least during prototype development.
     *
     * @throws ConfigException
     */
    public static function configure(): void
    {
        try {
            // Update network settings with timeout from user settings.
            self::$instance->network->setTimeout(
                timeout: UserSettingsRepository::getValue(field: Field::API_TIMEOUT)
            );

            // Override cache integration with None if cache is disabled in
            // user settings.
            if (!UserSettingsRepository::isEnabled(field: Field::CACHE_ENABLED)) {
                self::$instance->cache = new None();
            }

            // Automatically update Ecom instance with data which require
            // user settings to be configured.
            if (UserSettingsRepository::hasUserCredentials()) {
                // Setup default JWT auth using config settings.
                if (self::$instance->jwtAuth === null) {
                    self::setJwtAuth(auth: new Jwt(
                        clientId: UserSettingsRepository::getClientId(),
                        clientSecret: UserSettingsRepository::getClientSecret(),
                    ));
                }

                // Fetch store id from config, of fallback to default, and apply
                // to Ecom instance.
                if (self::$instance->storeId === null) {
                    self::$instance->storeId = UserSettingsRepository::getValue(field: Field::STORE_ID);
                }
            }

            // If no logger is defined, abut logs are enabled and we've a log
            // dir specified in settings then configure a FileLogger instance.
            if (
                self::$instance->logger instanceof NoneLogger &&
                UserSettingsRepository::isEnabled(field: Field::LOG_ENABLED)
            ) {
                $logDir = UserSettingsRepository::getValue(field: Field::LOG_DIR);

                if ($logDir !== null && $logDir !== '') {
                    self::$instance->logger = new FileLogger(path: $logDir);
                }
            }
        } catch (Throwable $e) {
            self::getLogger()->error(message: $e);
        }
    }

    /**
     * Checks if JWT auth is configured
     */
    public static function hasJwtAuth(): bool
    {
        return isset(self::$instance->jwtAuth);
    }

    /**
     * Checks if there is a Config instance
     */
    public static function hasInstance(): bool
    {
        return isset(self::$instance);
    }

    /**
     * Clears active configuration
     */
    public static function unsetInstance(): void
    {
        self::$instance = null;
    }

    /**
     * @throws ConfigException
     */
    public static function validateInstance(): void
    {
        if (self::$instance === null) {
            throw new ConfigException(
                message: 'Config instance not set. Please run Config::setup()'
            );
        }
    }

    /**
     * @throws ConfigException
     */
    public static function getLogger(): LoggerInterface
    {
        self::validateInstance();
        return self::$instance->logger;
    }

    /**
     * @throws ConfigException
     */
    public static function getPaymentHistoryDataHandler(): DataHandlerInterface
    {
        self::validateInstance();
        return self::$instance->paymentHistoryDataHandler;
    }

    /**
     * @throws ConfigException
     */
    public static function getCache(): CacheInterface
    {
        self::validateInstance();
        return self::$instance->cache;
    }

    /**
     * @throws ConfigException
     */
    public static function getJwtAuth(): ?Jwt
    {
        self::validateInstance();
        return self::$instance->jwtAuth;
    }

    /**
     * Update JWT auth instance.
     */
    public static function setJwtAuth(Jwt $auth): void
    {
        self::$instance->jwtAuth = $auth;
    }

    /**
     * @throws ConfigException
     */
    public static function getUserAgent(): string
    {
        self::validateInstance();
        return self::$instance->network->userAgent;
    }

    /**
     * @throws ConfigException
     */
    public static function isProduction(): bool
    {
        self::validateInstance();
        return self::$instance->isProduction;
    }

    /**
     * @throws ConfigException
     */
    public static function getProxy(): string
    {
        self::validateInstance();
        return self::$instance->network->proxy;
    }

    /**
     * @throws ConfigException
     */
    public static function getProxyType(): int
    {
        self::validateInstance();
        return self::$instance->network->proxyType;
    }

    /**
     * @throws ConfigException
     */
    public static function getTimeout(): int
    {
        self::validateInstance();
        return self::$instance->network->timeout;
    }

    /**
     * @throws ConfigException
     */
    public static function getLanguage(): Language
    {
        self::validateInstance();

        if (self::$instance->language !== null) {
            return self::$instance->language;
        }

        try {
            $store = Repository::getConfiguredStore();
            self::$instance->language = $store?->getLanguage();
        } catch (Throwable $e) {
            self::getLogger()->error(message: $e);
        }

        return self::$instance->language ?? Language::EN;
    }

    /**
     * @throws ConfigException
     */
    public static function getCurrencySymbol(): string
    {
        self::validateInstance();
        return self::$instance->currencySymbol;
    }

    /**
     * @throws ConfigException
     */
    public static function getCurrencyFormat(): CurrencyFormat
    {
        self::validateInstance();
        return self::$instance->currencyFormat;
    }

    /**
     * @throws ConfigException
     */
    public static function getStoreId(): ?string
    {
        self::validateInstance();
        return self::$instance->storeId;
    }

    /**
     * @throws ConfigException
     */
    public static function getCacheWidgets(): ?bool
    {
        self::validateInstance();
        return self::$instance->cacheWidgets;
    }

    /**
     * @throws ConfigException
     */
    public static function getLocation(): ?Location
    {
        self::validateInstance();
        return self::$instance->location;
    }

    /**
     * Allow force late location.
     */
    public static function setLocation(Location $location): void
    {
        self::$instance->location = $location;
    }

    /**
     * Resolve path starting from the ECom root directory.
     *
     * @throws Exception If the path contains invalid traversal.
     */
    public static function getPath(string $dir = ''): string
    {
        $ecomRoot = dirname(path: __DIR__);

        // Prevent directory traversal by checking for '..'
        if (str_contains(haystack: $dir, needle: '..')) {
            throw new Exception(
                message: 'Invalid directory path. Directory traversal is not allowed.'
            );
        }

        // Resolve the final path relative to the ECom root
        return $ecomRoot . ($dir !== '' ? '/' . ltrim(
            string: $dir,
            characters: '/'
        ) : '');
    }

    /**
     * @throws ConfigException
     */
    public static function getSettingsReader(): ReaderInterface
    {
        self::validateInstance();
        return self::$instance->settingsReader;
    }

    /**
     * @throws ConfigException
     */
    public static function getSettingsMetadata(): Metadata
    {
        self::validateInstance();
        return self::$instance->settingsMetadata;
    }
}

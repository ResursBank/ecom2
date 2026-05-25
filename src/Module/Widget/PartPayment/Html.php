<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Widget\PartPayment;

use JsonException;
use ReflectionException;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\ApiException;
use Resursbank\Ecom\Exception\AuthException;
use Resursbank\Ecom\Exception\CacheException;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\CurlException;
use Resursbank\Ecom\Exception\FilesystemException;
use Resursbank\Ecom\Exception\TranslationException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Exception\Validation\MissingKeyException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Locale\Translator;
use Resursbank\Ecom\Lib\Log\Logger;
use Resursbank\Ecom\Lib\Model\AnnuityFactor\AnnuityInformation;
use Resursbank\Ecom\Lib\Model\PaymentMethod;
use Resursbank\Ecom\Lib\Model\PriceSignage\Cost;
use Resursbank\Ecom\Lib\UserSettings\Field;
use Resursbank\Ecom\Lib\Utilities\Price;
use Resursbank\Ecom\Lib\Widget\Widget;
use Resursbank\Ecom\Module\AnnuityFactor\Repository;
use Resursbank\Ecom\Module\UserSettings\Repository as UserSettingsRepository;
use Resursbank\Ecom\Module\Widget\ConsumerCreditWarning\Html as ConsumerCreditWarning;
use Resursbank\Ecom\Module\Widget\PartPayment\Traits\Common;
use Resursbank\Ecom\Module\Widget\ReadMore\Html as ReadMoreHtml;
use Throwable;

use function max;
use function sprintf;

/**
 * Renders Part payment widget HTML
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class Html extends Widget
{
    use Common;

    public const CACHE_KEY_PREFIX =
        'resursbank-ecom-widget-part-payment-html';

    /** @var Cost */
    public readonly Cost $cost;

    /** @var string */
    public readonly string $logo;

    /** @var string */
    public readonly string $content;

    /** @var ConsumerCreditWarning */
    public readonly ConsumerCreditWarning $warning;

    /** @var ReadMoreHtml */
    public readonly ReadMoreHtml $readMore;

    /** @var bool */
    public readonly bool $shouldDisplayCostExample;

    /**
     * starting cost for the part payment widget as the configuration of the
     * product / cart changes where this widget is used. The endpoint must sit
     * in your implementation, the JS method which uses this method can then
     * be called to fetch the starting cost (see the template of this widget).
     * @param bool $showCostExample Hides cost list and pricing example if set to false.
     * @throws ApiException
     * @throws AuthException
     * @throws CacheException
     * @throws ConfigException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws FilesystemException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws MissingKeyException
     * @throws ReflectionException
     * @throws Throwable
     * @throws TranslationException
     * @throws ValidationException
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function __construct(
        public readonly float $amount,
        public ?PaymentMethod $paymentMethod = null,
        public ?int $months = null,
        public readonly bool $displayInfoText = true,
        public readonly bool $useLegacyReadMoreLink = false,
        public readonly bool $showCostExample = true
    ) {
        $this->populateFromSettings();

        $this->cost = $this->getCost(
            paymentMethod: $this->paymentMethod,
            amount: $this->amount,
            months: $this->months
        );

        $this->shouldDisplayCostExample = $this->shouldDisplayCostExample(
            threshold: $this->threshold,
            cost: $this->cost,
            paymentMethod: $this->paymentMethod,
            showCostExample: $this->showCostExample,
            amount: $this->amount
        );

        $this->logo = (string) file_get_contents(
            filename: __DIR__ . '/resurs.svg'
        );
        $this->readMore = new ReadMoreHtml(
            paymentMethod: $this->paymentMethod,
            amount: $this->amount,
            useLegacyLink: $this->useLegacyReadMoreLink
        );
        $this->warning = new ConsumerCreditWarning(
            paymentMethod: $this->paymentMethod,
            visible: $this->showCostExample
        );

        $this->content = $this->render(
            file: $this->getWidgetName() . DIRECTORY_SEPARATOR . 'templates' .
            DIRECTORY_SEPARATOR . 'html.phtml'
        );
    }

    /**
     * Determine whether the widget should render.
     *
     * Checks that part payment is enabled in user settings, a payment
     * method is configured, and the amount falls within the method's
     * min/max limits.
     */
    public function shouldRender(): bool
    {
        try {
            return
                $this->paymentMethod !== null &&
                UserSettingsRepository::isEnabled(
                    field: Field::PART_PAYMENT_ENABLED
                ) &&
                $this->amount >= $this->paymentMethod->getMinLimit() &&
                $this->amount <= $this->paymentMethod->getMaxLimit();
        } catch (Throwable $error) {
            Logger::error(message: $error);
        }

        return false;
    }

    /**
     * Fetches translated and formatted "Starting at %1 per month..." string.
     *
     * @throws ConfigException
     */
    public function getStartingAt(): string
    {
        if (!$this->shouldDisplayCostExample) {
            return $this->getNotEligibleMessage();
        }

        try {
            return str_replace(
                search: ['%1', '%2', '%3'],
                replace: [
                    $this->getFormattedCost(cost: $this->cost->monthlyCost),
                    $this->cost->durationMonths,
                    $this->cost->interest,
                ],
                subject: Translator::translate(phraseId: 'starting-at')
            );
        } catch (Throwable $e) {
            Config::getLogger()->error(message: $e);
            return '';
        }
    }

    /**
     * Fetches translated and formatted "Total %1" string.
     *
     * @throws ConfigException
     */
    public function getTotalCost(): string
    {
        try {
            return str_replace(
                search: ['%1', '%2'],
                replace: [
                    $this->cost->durationMonths,
                    $this->getFormattedCost(cost: $this->cost->totalCost)
                ],
                subject: Translator::translate(
                    phraseId: 'part-payment-total-cost'
                )
            );
        } catch (Throwable $e) {
            Config::getLogger()->error(message: $e);
            return '';
        }
    }

    /**
     * Fetches translated and formatted setup fee string.
     *
     * @throws ConfigException
     */
    public function getSetupFee(): string
    {
        try {
            return Translator::translate(phraseId: 'setup-fee') . ': ' .
                $this->getFormattedCost(cost: $this->cost->setupFee);
        } catch (Throwable $e) {
            Config::getLogger()->error(message: $e);
            return '';
        }
    }

    /**
     * Fetches translated and formatted administration fee string.
     *
     * @throws ConfigException
     */
    public function getAdministrationFee(): string
    {
        try {
            return Translator::translate(
                phraseId: 'administration-fee'
            ) . ': ' .
                $this->getFormattedCost(cost: $this->cost->administrationFee);
        } catch (Throwable $e) {
            Config::getLogger()->error(message: $e);
            return '';
        }
    }

    /**
     * @throws ConfigException
     */
    public function getNotEligibleMessage(): string
    {
        $result = '';
        $period = $this->getLongestPeriodWithZeroInterest();

        if ($period === 0) {
            return $result;
        }

        try {
            $result = sprintf(
                Translator::translate(phraseId: 'rb-pp-not-eligible'),
                $period
            );
        } catch (Throwable $e) {
            Config::getLogger()->error(message: $e);
        }

        return $result;
    }

    /**
     * Find the longest period with zero interest.
     *
     * @return int Longest zero interest period or 0 if none exists.
     * @throws ConfigException
     */
    public function getLongestPeriodWithZeroInterest(): int
    {
        try {
            $annuityFactors = Repository::getAnnuityFactors(
                paymentMethodId: $this->paymentMethod->id
            );
        } catch (Throwable $e) {
            Config::getLogger()->error(message: $e);
            return 0;
        }

        $longestPeriod = 0;

        /** @var AnnuityInformation $annuityFactor */
        foreach ($annuityFactors as $annuityFactor) {
            if ($annuityFactor->interest > 0.0) {
                continue;
            }

            $longestPeriod = max(
                $annuityFactor->durationMonths,
                $longestPeriod
            );
        }

        return $longestPeriod;
    }

    /**
     * Fetches formatted starting at cost with currency symbol.
     *
     * @throws ConfigException
     */
    public function getFormattedCost(float $cost): string
    {
        return Price::format(value: $cost);
    }
}

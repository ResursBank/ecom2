<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\AnnuityFactor\Http;

use JsonException;
use ReflectionException;
use Resursbank\Ecom\Exception\ApiException;
use Resursbank\Ecom\Exception\AuthException;
use Resursbank\Ecom\Exception\CacheException;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\CurlException;
use Resursbank\Ecom\Exception\HttpException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Http\Controller;
use Resursbank\Ecom\Lib\Validation\StringValidation;
use Resursbank\Ecom\Module\AnnuityFactor\Models\AnnuityInformation;
use Resursbank\Ecom\Module\AnnuityFactor\Models\DurationsByMonthRequest;
use Resursbank\Ecom\Module\AnnuityFactor\Repository;
use Throwable;

use function json_encode;

/**
 * Base controller class to handle operations associated with fetching annuity factor durations
 */
class DurationsByMonthController extends Controller
{
    /**
     * @throws ApiException
     * @throws AuthException
     * @throws CacheException
     * @throws ConfigException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws Throwable
     * @throws ValidationException
     */
    public function exec(
        string $storeId,
        string $paymentMethodId
    ): string {
        $stringValidation = new StringValidation();
        $return = [];

        if ($storeId === '') {
            throw new IllegalValueException(message: 'No storeId available');
        }

            $stringValidation->isUuid(value: $paymentMethodId);

            $annuityFactors = Repository::getAnnuityFactors(
                storeId: $storeId,
                paymentMethodId: $paymentMethodId
            );

            /** @var AnnuityInformation $annuityFactor */
        foreach ($annuityFactors->content as $annuityFactor) {
            $return[$annuityFactor->durationMonths] = $annuityFactor->paymentPlanName;
        }

        return json_encode(
            value: $return,
            flags: JSON_THROW_ON_ERROR | JSON_FORCE_OBJECT
        );
    }

    /**
     * @throws ConfigException
     * @throws HttpException
     */
    public function getRequestData(): DurationsByMonthRequest
    {
        $result = $this->getRequestModel(model: DurationsByMonthRequest::class);

        if (!$result instanceof DurationsByMonthRequest) {
            throw new HttpException(
                message: $this->translateError(phraseId: 'invalid-post-data'),
                code: 415
            );
        }

        return $result;
    }
}

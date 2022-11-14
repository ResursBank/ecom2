<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Http;

use Exception;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\HttpException;
use Resursbank\Ecom\Lib\Locale\Translator;

use function is_string;
use function strlen;

/**
 * Base controller class for JSON implementation. Execute arbitrary code,
 * construct a response as JSON encoded data.
 */
class Controller
{
    /**
     * Output JSON data.
     *
     * @param array $data
     * @param int $code
     * @return void
     * @todo This method lacks some test coverage since PHPUnit prevents testing methods that manipulate headers.
     */
    public function respond(
        array $data,
        int $code = 200,
    ): void {
        try {
            $result = json_encode(value: $data, flags: JSON_THROW_ON_ERROR);
        } catch (Exception) {
            $result = '{"error":"' . $this->translateError(phraseId: 'failed-to-encode') . '"}';
        }

        header(header: 'Content-Type: application/json');
        header(header: 'Content-Length: ' . strlen(string: $result));
        http_response_code(response_code: $code);

        echo $result;
    }

    /**
     * Mask messages from exceptions other than HttpException instances, to
     * ensure sensitive information is never rendered to the end client.
     *
     * @param Exception $exception
     * @return string
     */
    public function getErrorMessage(
        Exception $exception
    ): string {
        return $exception instanceof HttpException ?
            $exception->getMessage() :
            $this->translateError(phraseId: 'unknown-error');
    }

    /**
     * Resolve none-empty POST parameter.
     *
     * @param string $param
     * @return string
     * @throws HttpException
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function getPostParam(
        string $param
    ): string {
        if (!isset($_POST[$param])) {
            throw new HttpException(
                message: $this->translateError(phraseId: 'missing-post-param') . " $param",
                code: 404
            );
        }

        $result = is_string(value: $_POST[$param]) ? $_POST[$param] : '';

        if ($result === '') {
            throw new HttpException(
                message: $this->translateError(phraseId: 'empty-post-param') . " $param",
                code: 411
            );
        }

        return $result;
    }

    /**
     * @param Exception $exception
     * @return void
     */
    public function log(
        Exception $exception
    ): void {
        try {
            Config::getLogger()->debug(message: $exception);
        } catch (Exception) {
            // Logging is optional. Silence.
        }
    }

    /**
     * Translate error message without tossing Exception.
     *
     * @param string $phraseId
     * @return string
     */
    public function translateError(
        string $phraseId
    ): string {
        try {
            $result = Translator::translate(phraseId: $phraseId);
        } catch (Exception) {
            $result = 'Failed to translate error. Check debug log for info.';
        }

        return $result;
    }
}

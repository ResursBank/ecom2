<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Http;

use Exception;
use JsonException;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\HttpException;
use Resursbank\Ecom\Lib\Locale\Translator;
use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Utilities\DataConverter;

use function file_get_contents;
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
	 * Shorthand method to log an Exception and create an error response.
	 *
	 * @param Exception $exception
	 * @return void
	 */
	public function respondWithError(Exception $exception): void
	{
		$this->log(exception: $exception);
		$this->respond(
			data: ['error' => $this->getErrorMessage(exception: $exception)],
			code: 400
		);
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
	 * Resolve decoded input data.
	 *
	 * @param string $model
	 *
	 * @return Model
	 * @throws HttpException
	 * @todo Write tests for this. See ECP-271
	 */
	public function getRequestModel(
		string $model
	): Model {
		$data = file_get_contents(filename: 'php://input');

		if (false === $data) {
			throw new HttpException(
				message: $this->translateError(phraseId: 'missing-post-data'),
				code: 400
			);
		}

		try {
			$data = json_decode(
				json: $data,
				associative: false,
				depth: 512,
				flags: JSON_THROW_ON_ERROR
			);
		} catch (JsonException) {
			throw new HttpException(
				message: $this->translateError(phraseId: 'malformed-post-data'),
				code: 406
			);
		}

		try {
			return DataConverter::stdClassToType(
				object: $data,
				type: $model
			);
		} catch (Exception) {
			throw new HttpException(
				message: $this->translateError(phraseId: 'invalid-post-data'),
				code: 415
			);
		}
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

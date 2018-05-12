<?php

class BadInputException extends Exception {

	protected $input = '';
	protected $params = [];

	const MAX_LENGHT = 3;
	const BAD_EMAIL_FORMAT = 5;

	/**
	 * Construct new exception
	 * @param string $input
	 * @param int $code
	 * @param array $params
	 * @param string $message
	 * @return \BadInputException
	 */
	public static function newException(string $input, int $code, array $params = [], string $message = ''): BadInputException {
		$exception = new self($message, $code);
		$exception->input = $input;
		$exception->params = $params;

		return $exception;
	}

	/**
	 * Get name of bad input
	 * @return string
	 */
	public function getInput(): string {
		return $this->input;
	}

	/**
	 * Get params
	 * @return array
	 */
	public function getParams(): array {
		return $this->params;
	}

	/**
	 * Return printable message what is bad
	 * @return string
	 */
	public function getInputMessage(): string {
		return 'Input `' . $this->input . '` ' . self::codeToText($this->getCode(), $this->getParams()) . '.' . $this->getMessage();
	}

	/**
	 * Return text represent exception code
	 * @param int $code
	 * @param array $params
	 * @return string
	 */
	static function codeToText(int $code, array $params): string {
		switch($code) {
			case self::BAD_EMAIL_FORMAT:
				return 'must be valid email address';
			case self::MAX_LENGHT:
				return 'must be maximal ' . $params[0] . ' chars lenght';
			default:
				return '';
		}
	}

}

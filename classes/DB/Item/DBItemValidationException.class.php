<?php
/**
 * Definition for DBItemValidationException
 *
 */

/**
 * Exception for a validation error.
 * @author Korbibian Kapsner
 * @package DB\Item
 */
class DBItemValidationException extends UnexpectedValueException{
	/**
	 * Error code if the value was null where it was not allowed
	 */
	const WRONG_NULL = 0;
	/**
	 * Error code if the value was the wrong type
	 */
	const WRONG_TYPE = 1;
	/**
	 * Error code if the value was wrong. E.g. if the field is a enum and the value was not allowed.
	 */
	const WRONG_VALUE = 2;
	/**
	 * Error code if the value did not match the provided regular expression.
	 */
	const WRONG_REGEXP = 3;
	/**
	 * Error code if the value is missing but required
	 */
	const WRONG_MISSING = 4;
}

?>

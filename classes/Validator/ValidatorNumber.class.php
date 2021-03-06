<?php
/**
 * ValidatorNumber definition file
 */

/**
 * Validator for numbers. No instance class.
 *
 * @author Korbinian Kapsner
 * @package Validator
 */
class ValidatorNumber{

	/**
	 * Private constructor for ValidatorNumber. No way to create an instance.
	 */
	private function __construct(){}

	/**
	 * Checks if a value is an integer, a float that can berepresented as an integer or a string
	 * @param type $value
	 * @return bool
	 *
	 * @assert (10) === true
	 * @assert (-10) === true
	 * @assert (10.1) === false
	 * @assert (10.0) === true
	 * @assert (-10.0) === true
	 * @assert ("10") === true
	 * @assert ("-10") === true
	 * @assert ("-01f") === false
	 */
	public static function isIntLike($value){
		if (is_int($value) ||
			(
				is_float($value) &&
				((int) $value == $value)
			)
		){
			return true;
		}
		if (is_string($value)){
			if ($value[0] === "-"){
				$value[0] = "0";
			}
			return ctype_digit($value);
		}
		return  false;
	}

	/**
	 * Checks if a variable is a number or a number like string (just calls is_numeric()).
	 * @param type $value
	 * @return type
	 */
	public static function isFloatLike($value){
		return is_numeric($value);
	}

	/**
	 * Checks if the number $value is in the range between $min and $max. If one is omitted or NULL this border is not taken in account.
	 * @param type $value
	 * @param type $min
	 * @param type $max
	 * @return bool
	 *
	 * @assert (10, 0, 10) === true
	 * @assert (10, 10, 20) === true
	 * @assert (10, 11, 20) === false
	 * @assert (10, 0, 9) === false
	 * @assert (10, 0) === true
	 * @assert (10, 11) === false
	 * @assert (10, null, 10) === true
	 * @assert (10, null, 9) === false
	 */
	public static function isNumberInRange($value, $min = null, $max = null){
		if (self::isFloatLike($value)){
			if ($min === null || !self::isFloatLike($min)){
				$min = $value;
			}
			if ($max === null || !self::isFloatLike($max)){
				$max = $value;
			}
			if ($min <= $value && $max >= $value){
				return true;
			}
		}
		return false;
	}
}

?>

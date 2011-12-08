<?php

/**
 * Description of ValidatorTime
 *
 * @author kkapsner
 */
class ValidatorTime{
	private static $instance;
	private function __construct(){}

	/**
	 * Checks for a valid date.
	 * @param type $y year
	 * @param type $m month after january (0 - 11)
	 * @param type $d day of month (1 - ...)
	 * @return bool isValid
	 * 
	 * @assert (1985, 0, 31) === true
	 * @assert (1985, 3, 31) === false
	 * @assert (1985, 7, 31) === true
	 * @assert (1985, 1, 29) === false
	 * @assert (1904, 1, 29) === true
	 * @assert (1900, 1, 29) === false
	 * @assert (2000, 1, 29) === true
	 * @assert (1985, -1, 1) === false
	 * @assert (1985, 12, 1) === false
	 * @assert (1985, 1, 0) === false
	 * @assert ("1985h", 1, 0) === false
	 * @assert ("1985", "j1", 0) === false
	 * @assert ("1985", "12", "31") === true
	 * @assert (1985, 0, "test") === false
	 */
	public static function validDate($y, $m, $d){
		if (!ValidatorNumber::isIntLike($y) ||
			!ValidatorNumber::isIntLike($m) ||
			!ValidatorNumber::isIntLike($d) ||
			!ValidatorNumber::isNumberInRange($m, 0, 11)
		){
			return false;
		}
		$m = (int) $m + 1;
		$isLeap = ($y % 4 === 0) && ($y % 100 !== 0 || $y % 400 === 0);
		$dm = $m == 2?
			($isLeap? 29: 28):
			(
				30 + (($m & 1) ^ (($m & 0x08) >> 3))
			);
		if (!ValidatorNumber::isNumberInRange($d, 1, $dm)){
			return false;
		}
		return true;
	}

	/**
	 * Checks for a valid time
	 * @param type $h
	 * @param type $m
	 * @param type $s
	 * @param type $ms
	 * @param bool $oneDay if the time is the time within one day (this means 0 <= $h <= 23)
	 * @return bool isValid
	 * 
	 * @assert ("24", 0, 0, 0, true) === false
	 * @assert ("24", 0, 0, 0, false) === true
	 * @assert (-1, -1, -1, -1, false) === false
	 * @assert (-1, 0, 0, 0, false) === true
	 * @assert (0, 0, 0, 0, true) === true
	 */
	public static function validTime($h, $m, $s, $ms = 0, $oneDay = true){
		return
			ValidatorNumber::isIntLike($h) &&
			ValidatorNumber::isIntLike($m) &&
			ValidatorNumber::isIntLike($s) &&
			ValidatorNumber::isIntLike($ms) &&
			(!$oneDay || ValidatorNumber::isNumberInRange($h, 0, 23)) &&
			ValidatorNumber::isNumberInRange($m, 0, 59) &&
			ValidatorNumber::isNumberInRange($s, 0, 59) &&
			ValidatorNumber::isNumberInRange($ms, 0, 999)
		;
	}
}

?>

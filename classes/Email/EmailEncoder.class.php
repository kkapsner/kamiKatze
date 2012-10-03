<?php
/**
 * EmailEncoder definition file
 */

/**
 * Class for Email encoding issues. No instance class.
 *
 * @author Korbinian Kapsner
 * @package Email
 */
class EmailEncoder{
	/**
	 * Private constructor for EmailEncoder. No need and way to get an instance.
	 */
	private function __construct(){}

	/**
	 * Used maximal line length in the different encodings. This should NOT be set to less than 1.
	 * @var int
	 */
	public static $maxLineLength = 76;

	/**
	 * The default charset used in EmailEncoder::q.
	 * @var string
	 */
	public static $defaultCharset = "utf-8";

	/**
	 * Encodes the given str with the quoted printable algorithm (RFC 2045).
	 * Encode !"#$@[\]^`{|}~ not directly (see note in RFC 2045).
	 * @link http://tools.ietf.org/html/rfc2045
	 * @param string $str the string to encode
	 * @param bool $meanfullLinebreaks = true if the line-breaks in string should be NOT encoded
	 */
	public static function quotedPrintable($str, $meanfullLinebreaks = true){
		$maxLineLength = self::$maxLineLength;
		
		$lines = $meanfullLinebreaks? preg_split("/(?:\\r\\n|\\r|\\n)/", $str): array($str);

		$encoded = "";
		$cLines = count($lines);
		foreach ($lines as $i => $line){
			$len = strlen($line);
			$remainingChars = $maxLineLength;
			for ($j = 0; $j < $len; $j++){
				$char = $line[$j];
				$cLen = 1;
				if (!(
						($char >= "#" && $char <= "<") ||
						($char === ">"  || $char === "?") ||
						($char >= "A" && $char <= "Z") ||
						($char === "_") ||
						($char >= "a" && $char <= "z") ||
						($j < $len - 1 && ($char === "\t" || $char === " ")) # not SPACE or HTAB directly at the end of a line.
					)
				){
					$char = "=" . strToUpper(bin2hex($char));
					$cLen = 3;
				}
				$remainingChars -= $cLen;
				if ($remainingChars < 1){ # we may have to add an "="
					if ($j !== $len - 1 || $remainingChars < 0){
						$encoded .= "=" . Email::newLine;
						$remainingChars = $maxLineLength;
					}
				}
				$encoded .= $char;
			}
			if ($i != $cLines - 1){
				$encoded .= Email::newLine;
			}
		}

		return $encoded;
	}
	
	/**
	 * "quotes" the string (RFC 5322 Section 3.2.4)
	 * @link http://tools.ietf.org/html/rfc5322#section-3.2.4
	 * @param string $str
	 * @return string quoted string
	 */
	public static function quotedString($str){
		return '"' . str_replace(array('"', '\\'), array('\"', '\\\\'), $str) . '"';
	}
	
	/**
	 * Related encoding to quoted-printable. (RFC 2047)
	 * @link http://tools.ietf.org/html/rfc2047
	 * @param string $value
	 * @param int $alreadyConsumedChars
	 * @param string $charset
	 * @return string the encoded value with desired folding. 
	 */
	public static function q($value, $alreadyConsumedChars = 0, $charset = NULL){
		$maxLineLength = self::$maxLineLength;
		if ($charset === NULL){
			$charset = self::$defaultCharset;
		}

		$ret = "";
		$encodingString = "=?" . $charset . "?Q?";
		$encodingLen = strlen($encodingString);
		if ($alreadyConsumedChars > 1 && $alreadyConsumedChars + $encodingLen + 2 + 3 > $maxLineLength){
			$ret .= Email::newLine . " ";
			$alreadyConsumedChars = 1;
		}

		$ret .= $encodingString;
		$alreadyConsumedChars += $encodingLen;
		$remaining = $maxLineLength - $alreadyConsumedChars - 2; # the 2 are the ?= at the end

		$offset = 0;
		$vLen = strlen($value);
		for ($i = 0; $offset + $i < $vLen; $i++){
			$char = mb_strcut($value, $offset + $i, 1, $charset);
			$cLen = strlen($char);
			if ($cLen === 0){
				for ($cLen = 1; $char === "";){
					$char = mb_strcut($value, $offset + $i, ++$cLen, $charset);
				}
			}

			if ($cLen > 1 || !(ctype_alnum($char) || strpos("!*+-/ ", $char) !== false)){
				$quoted = "";
				for ($j = 0; $j < $cLen; $j++){
					$quoted .= "=" . strtoupper(bin2hex($char[$j]));
				}
				$char = $quoted;
				$i += $cLen - 1;
				$cLen *= 3;
			}
			$remaining -= $cLen;

			if ($char === " "){
				$char = "_";
			}

			if ($remaining < 0 && $i !== 0){
				$ret .= "?=" . Email::newLine . " " . $encodingString;
				$offset += $i;
				$i = 0;
				$remaining = $maxLineLength - $encodingLen - 3 - $cLen; #3 is the ending ?= and the space at the beginning.
			}
			$ret .= $char;
		}

		return $ret .= "?=";
	}

	/**
	 * Folds the value.
	 * @param string $value
	 * @param int $alreadyConsumedChars
	 * @return string
	 */
	public static function fold($value, $alreadyConsumedChars = 0){
		$maxLineLength = self::$maxLineLength;

		# doing it with an array is ~2 times faster than going throught the string by hand.
		$parts = explode(" ", $value);
		$ret = "";

		#handle first element different
		$first = array_shift($parts);
		$len = strlen($first);
		if ($alreadyConsumedChars > 1 && $alreadyConsumedChars + $len > $maxLineLength){
			$ret .= Email::newLine . " ";
			$alreadyConsumedChars = 1;
		}
		$ret .= $first;
		$alreadyConsumedChars += $len;

		foreach ($parts as $part){
			$part = " " . $part;
			$len = strlen($part);
			if ($alreadyConsumedChars + $len > $maxLineLength){
				$ret .= Email::newLine;
				$alreadyConsumedChars = 0;
			}
			$ret .= $part;
			$alreadyConsumedChars += $len;
		}
		return $ret;
	}

	/**
	 * Escapes a header value if necessary
	 * @param string $value
	 * @param int $alreadyConsumedChars
	 * @param string $charset = EmailEncoder::$defaultCharset
	 * @return string
	 */
	public static function escapeHeaderValue($value, $alreadyConsumedChars = 0, $charset = NULL){
		if (preg_match('/^[a-z0-9 !\*+\-\/=_]*$/i', $value)){
			return self::fold($value, $alreadyConsumedChars);
		}
		else {
			return self::q($value, $alreadyConsumedChars, $charset);
		}
	}

	/**
	 * Escapes a string so it is a valid phrase (RFC 5322)
	 * @link http://tools.ietf.org/html/rfc5322#section-3.2.5
	 * @param string $value
	 * @param int $alreadyConsumedChars
	 * @param string $charset = EmailEncoder::$defaultCharset
	 * @return string
	 */
	public static function escapePhrase($value, $alreadyConsumedChars = 0, $charset = NULL){
		if (preg_match('/^[a-z0-9!#$%&\'\*\+\-\/=\?\^_`\{\|\}~ ]*$/i', $value)){
			return self::fold($value, $alreadyConsumedChars);
		}
		elseif (preg_match('/^[\x20-\x7E]*$/', $value)){
			return self::fold(self::quotedString($value), $alreadyConsumedChars);
		}
		else {
			return self::q($value, $alreadyConsumedChars, $charset);
		}
	}

	/**
	 * Calculates the lengh of the last line in $str. Line delimiter is Email::newLine
	 * @param string $str
	 * @param int $firstLineAlreadyConsumed
	 * @return int
	 */
	public static function getLastLineLength($str, $firstLineAlreadyConsumed = 0){
		$len = strlen($str);
		$pos = strrpos($str, Email::newLine);
		if ($pos === false){
			return $len + $firstLineAlreadyConsumed;
		}
		else {
			return $len - $pos - strlen(Email::newLine);
		}
	}

	/**
	 * Calculates the lengh of the first line in $str. Line delimiter is Email::newLine
	 * @param string $str
	 * @param int $firstLineAlreadyConsumed
	 * @return int
	 */
	public static function getFirstLineLength($str, $firstLineAlreadyConsumed = 0){
		$len = strlen($str);
		$pos = strpos($str, Email::newLine);
		if ($pos === false){
			$pos = $len;
		}
		return $pos + $firstLineAlreadyConsumed;
	}

	/**
	 * Generates a boundary that is very likely to be a unique sequence in the email source code.
	 * @return string
	 */
	public static function generateBoundary(){
		return "=-" . md5(uniqid(rand(), true));
	}

	/**
	 * Generates an ID.
	 * @return string
	 */
	public static function generateID(){
		return  md5(uniqid(rand(), true));
	}
}

?>

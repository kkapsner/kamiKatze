<?php
/**
 * EmailHeader definition file
 */

/**
 * Representation of an email header.
 *
 * @author Korbinian Kapsner
 * @package Email\Header
 */
class EmailHeader{
	/**
	 * header name
	 * @var string
	 */
	protected $name;

	/**
	 * header value
	 * @var string
	 */
	protected $value;

	/**
	 * charset of the value. Needed if the value needs to be encoded. Default: EmailEncoder::$defaultCharset
	 * @var string
	 */
	public $valueCharset = NULL;

	/**
	 * Constructor of Emailheader
	 *
	 * @param string $name The header name
	 * @param string $value The header value
	 */
	public function __construct($name, $value = ""){
		$this->setName($name);
		$this->setValue($value);
	}

	/**
	 * Sets the name of the header.
	 * @param type $name
	 * @return bool if the name was valid and set.
	 */
	public function setName($name){
		if (preg_match('/^[\x21-\x39\x3B-\x7E]+$/', $name) && strlen($name) < EmailEncoder::$maxLineLength - 2){
			$this->name = $name;
			return true;
		}
		return false;
	}

	/**
	 * Returns the name of the header.
	 * @return string
	 */
	public function getName(){
		return $this->name;
	}

	/**
	 * Sets the value of the header.
	 * @param string $value
	 * @return bool if the value was valid and set.
	 */
	public function setValue($value){
		if (is_string($value)){
			$this->value = $value;
			return true;
		}
		return false;
	}

	/**
	 * Returns the value of the header.
	 * @return mixed
	 */
	public function getValue(){
		return $this->value;
	}
	
	/**
	 * Calls EmailEncoder::escapeHeaderValue() to fold the value.
	 *
	 * @param type $alreadyConsumedChars number of chars not to be used in the first line
	 * @return string the folded value string. If a non printable character is in the value the value is encoded.
	 */
	public function getFoldedValue($alreadyConsumedChars = 0){
		return EmailEncoder::escapeHeaderValue($this->value, $alreadyConsumedChars, $this->valueCharset);
	}

	/**
	 * Returns the string representation of the header. CRLF is added at the end.
	 * @return string
	 */
	public function __toString(){
		return $this->name . ": " . $this->getFoldedValue(strlen($this->name) + 2) . Email::newLine;
	}

}

?>

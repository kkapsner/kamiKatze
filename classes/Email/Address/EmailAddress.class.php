<?php

/**
 * Description of EmailAddress
 *
 * @author kkapsner
 */
class EmailAddress implements EmailAddressInterface{
	public
		$address,
		$name;

	/**
	 * The charset of the Name. Default: EmailEncoder::$defaultCharset
	 * @var string
	 */
	public $charset = NULL;

	public function __construct($address, $name = ""){
		$this->address = $address;
		$this->name = $name;
	}

	/**
	 * Checks if the e-mail address is valid.
	 * @return bool
	 */
	public function isValid(){
		return filter_var($this->address, FILTER_VALIDATE_URL) !== false;
	}
	
	/**
	 * Returns the string representation of the address.
	 * @param int $alreadyConsumedChars
	 * @return string 
	 */
	public function toHeaderEncoded($alreadyConsumedChars = 0){
		if ($this->name){
			$escapedName = EmailEncoder::escapePhrase($this->name, $alreadyConsumedChars, $this->charset);
			$alreadyConsumedChars = EmailEncoder::getLastLineLength($escapedName, $alreadyConsumedChars);
			if ($alreadyConsumedChars + strlen($this->address) + 3 > EmailEncoder::$maxLineLength){
				$escapedName .= Email::newLine;
			}
			return $escapedName . " <" . $this->address . ">";
		}
		else {
			return $this->address;
		}
		
	}
	
	/**
	 * Returns the string representation of the address.
	 * @return string
	 */
	public function __toString(){
		if ($this->name){
			return $this->name . " <" . $this->address . ">";
		}
		else {
			return $this->address;
		}
	}
}

?>

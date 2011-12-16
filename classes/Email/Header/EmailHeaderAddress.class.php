<?php

/**
 * Description of EmailHeaderAddress
 *
 * @author kkapsner
 */
class EmailHeaderAddress extends EmailHeader{
	/**
	 * The maximum number of addresses in the header. No restriction if set to zero.
	 * @var int
	 */
	public $maxLength = 0;
	/**
	 * The value is an array of EmailAddress or EmailAddressGroup
	 * @var array
	 */
	protected $value = array();

	/**
	 *
	 * @param type $name
	 * @param EmailAddressInterface $firstAddress 
	 */
	public function __construct($name, EmailAddressInterface $firstAddress = NULL){
		$this->setName($name);
		if ($firstAddress){
			$this->addAddress($firstAddress);
		}
	}

	/**
	 * Do not use this function. It does nothing. Use addAddress and removeAddress instead.
	 * The way of getting the value-array by getValue and then manipulating it is dangerous.
	 * @param type $value
	 * @return false
	 */
	public function setValue($value){
		return false;
	}

	/**
	 * Adds the address to the address-array. An address can be added multiple times.
	 * @param EmailAddressInterface $address
	 * @return bool if the address was added.
	 */
	public function addAddress(EmailAddressInterface $address){
		if ($this->maxLength === 0 || count($this->value) < $this->maxLength){
			$this->value[] = $address;
			return true;
		}
		return false;
	}

	/**
	 * Removes the first ocurence of the address.
	 * @param EmailAddressInterface $address
	 * @return bool if the address occured and was removed.
	 */
	public function removeAddress(EmailAddressInterface $address){
		$pos = array_search($address, $this->value, true);
		if ($pos !== false){
			array_splice($this->value, $pos, 1);
			return true;
		}
		return false;
	}

	
	public function getFoldedValue($alreadyConsumedChars = 0){
		$ret = "";
		$first = true;
		foreach ($this->value as $value){
			if ($first){
				$first = false;
			}
			else {
				$ret .= "," . Email::newLine . " ";
				$alreadyConsumedChars = 1;
			}
			$ret .= $value->toHeaderEncoded($alreadyConsumedChars);
		}
		return $ret;
	}

	/**
	 * See EmailHeader::__toString(). Exception: if the $value contains no EmailAddressInterface instance it returns "" (empty string).
	 * @return string
	 */
	public function __toString(){
		if (count($this->value) === 0){
			return "";
		}
		return parent::__toString();
	}

}

?>

<?php

/**
 * Description of EmailHeaderParametric
 *
 * @author kkapsner
 */
class EmailHeaderParametric extends EmailHeader{
	/**
	 * Parameter of the content-type.
	 * @var array
	 */
	protected $parameter = array();
	
	public function __construct($name, $value = "", array $parameter = NULL){
		parent::__construct($name, $value);
		if ($parameter !== null){
			foreach ($parameter as $name => $v){
				$this->setParameter($name, $v);
			}
		}
	}

	public function getFoldedValue($alreadyConsumedChars = 0){
		$ret = $this->value;
		foreach ($this->parameter as $name => $value){
			$ret .= ";" . Email::newLine . " " . $name . "=" . EmailEncoder::quotedString($value);
		}
		return $ret;
	}

	public function setValue($value){
		if (preg_match('/^[^\x00-\x20;\x7E-\xFF]+$/i', $value)){
			$this->value = $value;
			return true;
		}
		return false;
	}

	/**
	 * Sets a parameter.
	 * @param string $name
	 * @param string $value
	 * @return bool if the parameter was set
	 */
	public function setParameter($name, $value){
		if (preg_match('/^[^\x00-\x20\x7F-\xFF()<>@,;:\\\\"\/\[\]?=]+$/', $name) &&
			preg_match('/^[\x20-\x7E]+$/', $value)
		){
			$this->parameter[$name] = $value;
		}
		return false;
	}

	/**
	 * Unsets a parameter.
	 * @param string $name
	 */
	public function unsetParameter($name){
		unset ($this->parameter[$name]);
	}
}

?>

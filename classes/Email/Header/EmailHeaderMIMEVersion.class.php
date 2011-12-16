<?php

/**
 * Description of EmailHeaderMIMEVersion
 *
 * @author kkapsner
 */
class EmailHeaderMIMEVersion extends EmailHeader{
	protected $name = "MIME-Version";
	public function __construct($value = "1.0"){
		parent::__construct("MIME-Version", $value);
	}

	public function getFoldedValue($alreadyConsumedChars = 0){
		return $this->value;
	}

	public function setName($name){
		return false;
	}

	public function setValue($value){
		if (preg_match('/\d+\.\d+/', $value)){
			$this->value = $value;
			return true;
		}
		return false;
	}

}

?>

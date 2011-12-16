<?php

/**
 * Description of EmailHeaderMessageID
 *
 * @author kkapsner
 */
class EmailHeaderMessageID extends EmailHeader{
	protected $name = "Message-ID";
	/**
	 * Domain of the ID.
	 * @var string
	 */
	public $domain = "byKKJS";
	public function __construct(){
		$this->value = EmailEncoder::generateID();
	}

	public function getFoldedValue($alreadyConsumedChars = 0){
		return "<" . $this->value . "@" . $this->domain . ">";
	}

	public function setName($name){
		return false;
	}

	public function setValue($value){
		return false;
	}

}

?>

<?php
/**
 * EmailHeadermessageID definition file
 */

/**
 * Email header for the message ID.
 *
 * @author Korbinian Kapsner
 * @package Email\Header
 */
class EmailHeaderMessageID extends EmailHeader{
	/**
	 * {@inheritdoc}
	 * @var string
	 */
	protected $name = "Message-ID";
	/**
	 * Domain of the ID.
	 * @var string
	 */
	public $domain = "byKKJS";

	/**
	 * Constructor of EmailHeaderMessageID
	 */
	public function __construct(){
		$this->value = EmailEncoder::generateID();
	}

	/**
	 * {@inheritdoc}
	 *
	 * @param int $alreadyConsumedChars
	 * @return string
	 */
	public function getFoldedValue($alreadyConsumedChars = 0){
		return "<" . $this->value . "@" . $this->domain . ">";
	}

	/**
	 * This function does nothing! It has to be implementet because of inheritance.
	 *
	 * @param string $name
	 * @return false
	 */
	public function setName($name){
		return false;
	}

	/**
	 * This function does nothing! It has to be implementet because of inheritance.
	 *
	 * @param string $value
	 * @return false
	 */
	public function setValue($value){
		return false;
	}

}

?>

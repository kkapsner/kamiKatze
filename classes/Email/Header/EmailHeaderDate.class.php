<?php
/**
 * EmailHeaderDate definition file
 */

/**
 * The date email header.
 *
 * @author Korbinian Kapsner
 * @package Email\Header
 */
class EmailHeaderDate extends EmailHeader{
	/**
	 * {@inheritdoc}
	 *
	 * @var string
	 */
	protected $name = "Date";

	/**
	 * Constructor of EmailHeaderDate
	 * @param int $value the UNIX timestamp
	 */
	public function __construct($value = null){
		if ($value === null){
			$value = time();
		}
		parent::__construct("Date", $value);
	}

	/**
	 * {@inheritdoc}
	 *
	 * @param int $alreadyConsumedChars
	 * @return string
	 */
	public function getFoldedValue($alreadyConsumedChars = 0){
		return date("r", $this->value);
	}

	/**
	 * This function does nothing! It has to be implementet because of inheritance.
	 *
	 * @param type $name
	 * @return false
	 */
	public function setName($name){
		return false;
	}

	/**
	 * Setter for the MIME-Version.
	 * 
	 * @param string $value the new MIME-version
	 * @return boolean if the MIME-version was set.
	 */
	public function setValue($value){
		if (is_int($value)){
			$this->value = $value;
			return true;
		}
		else {
			return false;
		}
	}

}
<?php
/**
 * EmailHeaderMIMVersion definition file
 */

/**
 * The MIME-version email header.
 *
 * @author Korbinian Kapsner
 * @package Email\Header
 */
class EmailHeaderMIMEVersion extends EmailHeader{
	/**
	 * {@inheritdoc}
	 *
	 * @var string
	 */
	protected $name = "MIME-Version";

	/**
	 * Constructor of EmailHeaderMIMEVersion
	 * @param string $value the MIME-Version
	 */
	public function __construct($value = "1.0"){
		parent::__construct("MIME-Version", $value);
	}

	/**
	 * {@inheritdoc}
	 *
	 * @param int $alreadyConsumedChars
	 * @return string
	 */
	public function getFoldedValue($alreadyConsumedChars = 0){
		return $this->value;
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
		if (preg_match('/\d+\.\d+/', $value)){
			$this->value = $value;
			return true;
		}
		else {
			return false;
		}
	}

}

?>

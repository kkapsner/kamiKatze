<?php
/**
 * EmailHeaderParametric
 */

/**
 * Representation of an email header with parameters.
 *
 * @author Korbinian Kapsner
 * @package Email\Header
 */
class EmailHeaderParametric extends EmailHeader{
	/**
	 * Parameter of the content-type.
	 * @var array
	 */
	protected $parameter = array();

	/**
	 * Constructor of EmailheaderParametric
	 *
	 * @param string $name the header name
	 * @param string $value the header value
	 * @param array $parameter the header parameters
	 */
	public function __construct($name, $value = "", array $parameter = NULL){
		parent::__construct($name, $value);
		if ($parameter !== null){
			foreach ($parameter as $name => $v){
				$this->setParameter($name, $v);
			}
		}
	}

	/**
	 * {@inheritdoc}
	 *
	 * @param int $alreadyConsumedChars
	 * @return string
	 * @todo use $alreadyConsumedChars
	 */
	public function getFoldedValue($alreadyConsumedChars = 0){
		$ret = $this->value;
		foreach ($this->parameter as $name => $value){
			$ret .= ";" . Email::newLine . " " . $name . "=" . EmailEncoder::quotedString($value);
		}
		return $ret;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @param string $value
	 * @return boolean
	 */
	public function setValue($value){
		if (preg_match('/^[^\x00-\x20;\x7E-\xFF]+$/i', $value)){
			$this->value = $value;
			return true;
		}
		else {
			return false;
		}
	}

	/**
	 * Sets a parameter.
	 *
	 * @param string $name
	 * @param string $value
	 * @return bool if the parameter was set
	 */
	public function setParameter($name, $value){
		if (preg_match('/^[^\x00-\x20\x7F-\xFF()<>@,;:\\\\"\/\[\]?=]+$/', $name) &&
			preg_match('/^[\x20-\x7E]+$/', $value)
		){
			$this->parameter[$name] = $value;
			return true;
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

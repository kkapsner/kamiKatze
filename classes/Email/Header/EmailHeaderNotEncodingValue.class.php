<?php
/**
 * EmailHeaderNotEncodingValue definition file
 */

/**
 * Email header that has a value that shall not be encoded.
 *
 * @author Korbinian Kapsner
 * @package Email\Header
 */
class EmailHeaderNotEncodingValue extends EmailHeader{
	/**
	 * {@inheritdoc}
	 * 
	 * @return string
	 */
	public function __toString(){
		return $this->name . ": " . $this->value . Email::newLine;
	}
}

?>

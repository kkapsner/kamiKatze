<?php

/**
 * Description of EmailHeaderNotEncodingValue
 *
 * @author kkapsner
 */
class EmailHeaderNotEncodingValue extends EmailHeader{
	public function __toString(){
		return $this->name . ": " . $this->value . Email::newLine;
	}
}

?>

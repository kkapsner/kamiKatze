<?php
/**
 * DBItemFieldBoolean definition file
 */

/**
 * Representation of an boolean field
 *
 * @author Korbinian Kapsner
 * @package DB\Item\Field
 */
class DBItemFieldBoolean extends DBItemField{

	/**
	 * {@inheritdoc}
	 * 
	 * @param mixed $value
	 * @return boolean
	 */
	public function isValidValue($value){
		return is_bool($value) || is_numeric($value);
	}
}

?>

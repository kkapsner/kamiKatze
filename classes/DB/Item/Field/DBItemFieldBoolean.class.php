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
class DBItemFieldBoolean extends DBItemFieldNative{
	/**
	 * String to be displayed if the field contains true.
	 * @var string
	 */
	public $trueString = "Yes";
	
	/**
	 * String to be displayed if the field contains false.
	 * @var string
	 */
	public $falseString = "No";

	/**
	 * {@inheritdoc}
	 * 
	 * @param DBItemClassSpecifier $classSpecifier
	 * @param mixed[] $options
	 */
	protected function parseOptions(DBItemClassSpecifier $classSpecifier, $options){
		parent::parseOptions($classSpecifier, $options);

		$this->trueString = array_read_key("trueString", $options, $this->trueString);
		$this->falseString = array_read_key("falseString", $options, $this->falseString);
	}
	
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

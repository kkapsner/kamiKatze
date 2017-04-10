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
	 * @param mixed[] $properties
	 */
	protected function adoptProperties(DBItemClassSpecifier $classSpecifier, $properties){
		parent::adoptProperties($classSpecifier, $properties);

		$this->trueString = array_read_key("trueString", $properties, $this->trueString);
		$this->falseString = array_read_key("falseString", $properties, $this->falseString);
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
	

	/**
	 * {@inheritdoc}
	 *
	 * @param type $value
	 * @return string|null If null is returned the field has no value to be stored in the original table.
	 */
	public function translateToDB($value){
		if ($value === null && $this->null){
			return "NULL";
		}
		else {
			return DB::getInstance()->quote($value? 1: 0, DB::PARAM_INT);
		}
	}
}
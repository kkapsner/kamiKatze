<?php
/**
 * DBItemFieldNumber definition file
 */

/**
 * Representation of an number field
 *
 * @author Korbinian Kapsner
 * @package DB\Item\Field
 */
class DBItemFieldNumber extends DBItemFieldNative{
	/**
	 * Step attribute to be used in the input
	 * @var String
	 */
	public $step = "any";
	
	/**
	 * Min attribute to be used in the input
	 * @var String
	 */
	public $min = false;
	
	/**
	 * Max attribute to be used in the input
	 * @var String
	 */
	public $max = false;

	/**
	 * {@inheritdoc}
	 * 
	 * @param DBItemClassSpecifier $classSpecifier
	 * @param mixed[] $properties
	 */
	protected function adoptProperties(DBItemClassSpecifier $classSpecifier, $properties){
		parent::adoptProperties($classSpecifier, $properties);

		$this->step = array_read_key("step", $properties, $this->step);
		$this->min = array_read_key("min", $properties, $this->min);
		$this->max = array_read_key("max", $properties, $this->max);
	}
	
	/**
	 * {@inheritdoc}
	 * 
	 * @param mixed $value
	 * @return boolean
	 */
	public function isValidValue($value){
		return is_numeric($value);
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
			return $this->getDB()->quote($value, DB::PARAM_STR);
		}
	}
}
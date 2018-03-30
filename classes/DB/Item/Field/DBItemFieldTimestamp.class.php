<?php
/**
 * DBItemFieldTimestamp definition file
 */

/**
 * Representation of a timestamp field
 *
 * @author Korbinian Kapsner
 * @package DB\Item\Field
 */
class DBItemFieldTimestamp extends DBItemFieldNative{
	/**
	 * Format of the display.
	 * @var string
	 */
	public $displayFormat = "Y-m-d H:i:s";
	
	/**
	 * Format of edit.
	 * @var string
	 */
	public $editFormat = "Y-m-d H:i:s";

	/**
	 * {@inheritdoc}
	 * 
	 * @param DBItemClassSpecifier $classSpecifier
	 * @param mixed[] $properties
	 */
	protected function adoptProperties(DBItemClassSpecifier $classSpecifier, $properties){
		parent::adoptProperties($classSpecifier, $properties);

		$this->displayFormat = array_read_key("displayFormat", $properties, $this->displayFormat);
		$this->editFormat = array_read_key("editFormat", $properties, $this->editFormat);
	}
	
	/**
	 * {@inheritdoc}
	 * 
	 * @param mixed $value
	 * @return boolean
	 */
	public function isValidValue($value){
		if ($value === null && $this->null){
			return "NULL";
		}
		return $value instanceof DateTime;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @param DBItem $item
	 * @return type
	 */
	public function getValue(DBItem $item){
		$realValue = $item->getRealValue($this);
		if ($realValue === null){
			return null;
		}
		else {
			return new DateTime($realValue);
		}
	}

	/**
	 * {@inheritdoc}
	 * 
	 * @param DBItem $item
	 * @param type $value
	 * @throws InvalidArgumentException
	 */
	public function setValue(DBItem $item, $value){
		if ($value === null){
			$item->setRealValue($this->name, $value);
		}
		elseif (!($value instanceof DateTime)){
			throw new InvalidArgumentException("Property " . $this->name . " is not a DateTime.");
		}
		else {
			$item->setRealValue($this->name, $value->format("Y-m-d H:i:s"));
		}
	}

	/**
	 * {@inheritdoc}
	 *
	 * @param array $data
	 * @param array $translatedData
	 */
	public function translateRequestData($data, &$translatedData){
		if (array_key_exists($this->name, $data)){
			$translatedData[$this->name] = DateTime::createFromFormat($this->editFormat, $data[$this->name]);
		}
	}
	

	/**
	 * {@inheritdoc}
	 *
	 * @param type $value
	 * @return string|null If null is returned the field has no value to be stored in the original table.
	 */
	public function translateToDB($value){var_dump($value);
		if ($value === null && $this->null){
			return "NULL";
		}
		else {
			return DB::getInstance()->quote($value, DB::PARAM_STR);
		}
	}
}
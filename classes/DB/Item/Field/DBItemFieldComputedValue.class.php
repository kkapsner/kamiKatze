<?php
/**
 * DBItemFieldCalculatedValue definition file
 */

/**
 * Representation of a field whichs value is completely calculated by PHP.
 *
 * @author Korbinian Kapsner
 * @package DB\Item\Field
 */
class DBItemFieldComputedValue extends DBItemField{

	/**
	 * {@inheritdoc}
	 * 
	 * @param mixed $value
	 * @return boolean
	 */
	public function isValidValue($value){
		return false;
	}

	/**
	 * {@inheritdoc}
	 * 
	 * @param DBItemClassSpecifier $classSpecifier
	 * @param mixed[] $properties
	 */
	protected function adoptProperties(DBItemClassSpecifier $classSpecifier, $properties){
		parent::adoptProperties($classSpecifier, $properties);
		
		// disable default options...
		$this->searchable = false;
		$this->editable = false;
		$this->regExp = null;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @param array $data
	 * @param array $translatedData
	 */
	public function translateRequestData($data, &$translatedData){
		if (array_key_exists($this->name, $data)){
			unset($translatedData[$this->name]);
		}
	}

	/**
	 * {@inheritdoc}
	 *
	 * DBItem field should only be set by member assignments.
	 * @param mixed $value
	 * @return null
	 */
	public function translateToDB($value){
		return null;
	}


	/**
	 * {@inheritdoc}
	 *
	 * @param DBItem $item
	 * @return null
	 */
	public function getValue(DBItem $item){
		return $item->{"get" . ucfirst($this->name)}();
	}

	/**
	 * {@inheritdoc}
	 *
	 * @param DBItem $item
	 * @param class $value
	 * @throws InvalidArgumentException
	 */
	public function setValue(DBItem $item, $value){
		throw new Exception("This field can not be set.");
	}

}

?>

<?php
/**
 * DBItemFieldGroup definition file
 */

/**
 * Representation of an array field
 *
 * @author Korbinian Kapsner
 * @package DB\Item\Field
 */
class DBItemFieldGroup extends DBItemField implements DBItemFieldHasSearchableSubcollection, DBItemFieldGroupInterface{
	use DBItemFieldGroupTrait;
	
	/**
	 * {@inheritdoc}
	 * 
	 * @param int $id
	 * @param mixed[] $values
	 */
	public function createDependencies($id, $values){
		if (array_key_exists($this->name, $values)){
			foreach ($this->groupFields as $field){
				$field->createDependencies($id, $values[$this->name]);
			}
		}
	}

	/**
	 * {@inheritdoc}
	 *
	 * @param DBItem $item
	 */
	public function deleteDependencies(DBItem $item){
		foreach ($this->groupFields as $field){
			$field->deleteDependencies($item);
		}
	}

	/**
	 * {@inheritdoc}
	 * 
	 * @param DBItem $item
	 * @return DBItemCollection
	 */
	public function getValue(DBItem $item){
		$ret = array();
		foreach ($this->groupFields as $field){
			$ret[$field->name] = $item->getRealValue($field);
		}
		return (object) $ret;
	}
	
	/**
	 * {@inheritdoc}
	 * 
	 * @param array $values
	 * @return array
	 */
	public function validate($values){
		$errors = array();
		if (array_key_exists($this->name, $values)){
			foreach ($this->groupFields as $field){
				$errors = array_merge($errors, $field->validate($values[$this->name]));
			}
		}
		return $errors;
	}

	/**
	 * {@inheritdoc}
	 * 
	 * @param type $value
	 * @return type
	 */
	public function isValidValue($value){
		if (is_a($value, "stdClass")){
			$value = (array) $value;
		}
		if (is_array($value)){
			foreach ($this->groupFields as $field){
				if (!array_key_exists($field->name, $value) || !$field->isValidValue($value[$field-name])){
					return false;
				}
			}
		}
		else {
			return false;
		}
	}

	/**
	 * {@inheritdoc}
	 * 
	 * @param DBItem $item
	 */
	public function saveDependencies(DBItem $item){
		foreach ($this->groupFields as $field){
			$field->saveDependencies($item);
		}
		return false;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @param DBItem $item
	 * @param type $value
	 */
	public function setValue(DBItem $item, $value){
		if (is_a($value, "stdClass")){
			$value = (array) $value;
		}
		foreach ($this->groupFields as $field){
			$field->setValue($item, $value[$field->name]);
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
			$translatedData[$this->name] = $this->groupFields->translateRequestData($data[$this->name]);
		}
	}

	/**
	 * {@inheritdoc}
	 * 
	 * @param mixed $value
	 * @param string $nameOut
	 * @param string|null $valueOut (optional) if this parameter is null the 
	 *        "name = value" string is appended to $nameOut.
	 */
	public function appendDBNameAndValueForCreate($value, &$nameOut, &$valueOut = null){
		if (is_a($value, "stdClass")){
			$value = (array) $value;
		}
		foreach ($this->groupFields as $field){
			$field->appendDBNameAndValueForCreate($value[$field->name], $nameOut, $valueOut);
		}
	}
}
<?php

/**
 * DBItemFieldCollection definition file
 */

/**
 * A collection of DBItemFields
 *
 * @author Korbinian Kapsner
 * @package DB\Item\Field
 */
class DBItemFieldCollection extends Collection{
	/**
	 * Cache to get the field by name faster.
	 * @var DBItemField[]
	 */
	private $nameToField = array();
	
	/**
	 * {@inheritdoc}
	 *
	 * @throws InvalidArgumentException
	 */
	public function __construct(){
		parent::__construct("DBItemField");
	}

	/**
	 * Translates a request data array to a data array with values to be assigned to a DBItem.
	 *
	 * @param array $data
	 * @param array $translatedData The array to store the translated data. If omitted an empty array is created.
	 * @return array The array containing the translated data.
	 */
	public function translateRequestData($data, &$translatedData = array()){
		foreach ($this as $field){
			/** @var DBItemField $field */
			$field->translateRequestData($data, $translatedData);
		}
		return $translatedData;
	}

	/**
	 * Validates a translated ({@see DBItemFieldCollection::translateRequestData()}) data array.
	 *
	 * @param array $values
	 * @return DBItemValidationException[]
	 */
	public function validate($values){
		$errors = array();
		foreach ($this as $field){
			/** @var DBItemField $field */
			$errors = array_merge($errors, $field->validate($values));
		}
		return $errors;
	}
	
	/**
	 * Searches in the collection for a field by its name. Returns the found
	 * field or NULL if no corresponding field is found.
	 * 
	 * @param String $name the field name
	 * @return null|DBItemField
	 */
	public function getFieldByName($name){
		return array_read_key($name, $this->nameToField, null);
	}
	
	public function offsetSet($offset, $value){
		parent::offsetSet($offset, $value);
		$this->nameToField[$value->name] = $value;
	}
	
	public function offsetUnset($offset){
		$item = $this[$offset];
		unset($this->nameToField[$item->name]);
		parent::offsetUnset($offset);
	}

	public function pop(){
		throw new BadMethodCallException("Pop is not available for DBItemFieldCollections.");
	}

	public function shift(){
		throw new BadMethodCallException("Shift is not available for DBItemFieldCollections.");
	}

	public function push($var){
		throw new BadMethodCallException("Push is not available for DBItemFieldCollections.");
	}

	public function splice($offset, $length = null, $replacement = null){
		throw new BadMethodCallException("Splice is not available for DBItemFieldCollections.");
	}

	public function unshift($var){
		throw new BadMethodCallException("Unshift is not available for DBItemFieldCollections.");
	}

}

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
			/* @var $field DBItemField */
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
			/* @var $field DBItemField */
			$errors = array_merge($errors, $field->validate($values));
		}
		return $errors;
	}
	
	/**
	 * Searches in the collection for a field by its name. Returns the found
	 * field or NULL if no corresponding field is found.
	 * 
	 * @param String $name the field name
	 * @return null|FBItemField
	 */
	public function getFieldByName($name){
		
		foreach ($this as $field){
			/* @var $field DBItemField */
			if ($field->name === $name){
				return $field;
			}
		}
		return null;
	}
}

?>
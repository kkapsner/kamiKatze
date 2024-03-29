<?php

/**
 * DBItemFieldGroupTrait definition file
 */

/**
 * Description of DBItemFieldGroupTrait
 *
 * @author kkapsner
 */
trait DBItemFieldGroupTrait{

	
	/**
	 * An array with the field options for the array entries.
	 * @var DBItemFieldCollection
	 */
	protected $groupFields = null;
	
	/**
	 * {@inheritdoc}
	 * 
	 * @param DBItemClassSpecifier $classSpecifier
	 * @param type $properties
	 */
	protected function adoptProperties(DBItemClassSpecifier $classSpecifier, $properties){
		if (array_key_exists("group", $properties)){
			$this->parseGroup($classSpecifier, $properties["group"]);
		}
		else {
			throw new BadMethodCallException("A DBItemFieldGroup needs a group property.");
		}
	}

	/**
	 * {@inheritdoc}
	 * 
	 * @param DBItemClassSpecifier $classSpecifier
	 * @param array $options
	 */
	public function parseGroup($classSpecifier, $group){
		if ($this->groupFields !== null){
			throw new BadMethodCallException("parseGroup can only be called once.");
		}
		$this->groupFields = self::iterateForParseClass($classSpecifier, $group);
		foreach ($this->groupFields as $field){
			/** @var DBItemField $field */
			$field->parentField = $this;
		}
	}

	/**
	 * {@inheritdoc}
	 * 
	 * @param mixed $value
	 * @return null
	 */
	public function translateToDB($value){
		return null;
	}

	/**
	 * {@inheritdoc}
	 * 
	 * @return null
	 */
	public function translateNameToDB(){
		return null;
	}
	
	/**
	 * {@inheritdoc}
	 * 
	 */
	public function getAllSubcollections(){
		return array($this->groupFields);
	}
	
	/**
	 * {@inheritdoc}
	 * 
	 * @param DBItem $item
	 */
	public function getSubcollection(DBItem $item){
		return $this->groupFields;
	}
}
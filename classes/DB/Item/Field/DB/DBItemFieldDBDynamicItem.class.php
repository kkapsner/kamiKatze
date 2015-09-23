<?php

/**
 * DBItemFieldDBDynamicItem definition file
 */

/**
 * Description of DBItemFieldDBDynamicItem
 *
 * @author kkapsner
 */
class DBItemFieldDBDynamicItem extends DBItemFieldDBItem implements DBItemFieldGroupInterface, DBItemFieldHasSearchableSubcollection{
	use DBItemFieldGroupTrait {
		DBItemFieldGroupTrait::parseGroup as traitParseGroup;
	}
	
	/**
	 * The field that holds the class.
	 * @var DBItemFieldEnum 
	 */
	protected $classField = null;
	
	/**
	 * The field that holds the id.
	 * @var DBItemField
	 */
	protected $idField = null;
	
	/**
	 * {@inheritdoc}
	 * 
	 * @param type $classSpecifier
	 * @param type $group
	 * @throws BadMethodCallException
	 */
	public function parseGroup($classSpecifier, $group){
		$this->traitParseGroup($classSpecifier, $group);
		
		if ($this->groupFields->count() !== 2){
			throw new BadMethodCallException("DBItemFieldDBDynamicItem needs exact two fields.");
		}
		foreach ($this->groupFields as $field){
			if ($field instanceof DBItemFieldEnum){
				$this->classField = $field;
			}
			else {
				$this->idField = $field;
			}
		}
		if (!$this->classField || !$this->idField){
			throw new BadMethodCallException("DBItemFieldDBDynamicItem needs exact one enum field.");
		}
		
		if (!$this->idField->null){
			throw new BadMethodCallException("DBItemFieldDBDynamicItem must be able to contain null.");
		}
	}
	
	/**
	 * {@inheritdoc}
	 * 
	 * 
	 * @param type $value
	 * @param type $propsOut
	 */
	public function appendDBNameAndValueForUpdate($value, &$propsOut){
		$this->idField->appendDBNameAndValueForUpdate($value, $propsOut);
	}

	public function isValidValue($value){
		if ($value !== null){
			$newClass = get_class($value);
			if ($this->classField->isValidValue($newClass)){
				$this->class = $newClass;
				return parent::isValidValue($value);
			}
			else {
				return false;
			}
		}
		else {
			return parent::isValidValue($value);
		}
	}

	public function translateRequestData($data, &$translatedData){
		if (
			array_key_exists($this->name, $data)
		){
			if (preg_match("/^([a-z_]+)#(\\d+)$/i", $data[$this->name], $matches) && class_exists($matches[1])){
				$translatedData[$this->name] = DBItem::getCLASS($matches[1], $matches[2]);
			}
			else {
				$translatedData[$this->name] = null;
			}
		}
	}

	protected function adoptProperties(DBItemClassSpecifier $classSpecifier, $properties){
		parent::adoptProperties($classSpecifier, $properties);
		
		$this->null = true;
		$this->searchable = false;
		
		if ($this->correlation !== self::N_TO_ONE){
			throw new BadMethodCallException("DBItemFieldDBDynamicItem only works with Nto1.");
		}
	}
	
	public function setValue(DBItem $item, $value){
		$oldClass = $this->class;
		$oldName = $this->name;
		
		if ($value !== null){
			$newClass = get_class($value);
			$oldValue = $this->getValue($item);
			if (!$oldValue || $newClass !== get_class($oldValue)){
				parent::setValue($item, null);
				$this->classField->setValue($item, $newClass);
			}
			$this->class = $newClass;
		}
		$this->name = $this->idField->name;
		parent::setValue($item, $value);
		
		$this->class = $oldClass;
		$this->name = $oldName;
	}
	
	public function getValue(DBItem $item){
		$oldClass = $this->class;
		$oldName = $this->name;
		
		$this->class = $this->classField->getValue($item);
		$this->name = $this->idField->name;
		$ret = parent::getValue($item);
		
		$this->class = $oldClass;
		$this->name = $oldName;
		return $ret;
	}
	
	public function getAllSubcollections(){
		return array($this->groupFields);
	}

	public function getSubcollection(DBItem $item){
		return $this->groupFields;
	}

}

?>

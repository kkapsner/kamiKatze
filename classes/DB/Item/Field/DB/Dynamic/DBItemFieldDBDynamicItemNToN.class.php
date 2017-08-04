<?php

/**
 * DBItemFieldDBDynamicItemNToN definition file
 */

/**
 * Description of DBItemFieldDBDynamicItemNToN
 *
 * @author kkapsner
 */
class DBItemFieldDBDynamicItemNToN extends DBItemFieldDBItemXToN {
	/**
	 * The sub fields representing all the different classes
	 * @var DBItemFieldDBItemBToB[]
	 */
	public $subFields = null;
	
	/**
	 * {@inheritdoc}
	 * 
	 * @param string|string[] $newClass
	 */
	public function setClass($newClass){
		if (!is_array($newClass)){
			throw new BadMethodCallException("Dynamic NtoN needs an associative array as class.");
		}
		
		$this->subFields = array();
		foreach ($newClass as $className => $properties){
			$properties["class"] = $className;
			$properties["correlation"] = self::N_TO_N;
			$this->subFields[$className] = new DBItemFieldDBItemNToN($this->name);
			$this->subFields[$className]->adoptProperties($this->parentClassSpecifier, $properties);
		}
		
		$this->class = array_keys($newClass);
		$this->classSpecifier = array_map(array("DBItemClassSpecifier", "make"), $this->class);
	}
	
	/**
	 * {@inheritdoc}
	 * 
	 * @param mixed $value
	 * @return boolean
	 */
	public function isValidValue($value){
		if (!parent::isValidValue($value)){
			return false;
		}
		if (is_a($value, "Collection") && $value->getClass() === "DBItemCollection"){
			foreach ($value as $i => $collection){
				$ok = true;
				foreach ($collection as $item){
					if (!is_a($item, $this->class[$i])){
						$ok = false;
						break;
					}
				}
				return $ok;
			}
		}
		else {
			return false;
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
			$data = $data[$this->name];
			if (is_array($data) && array_key_exists("present", $data)){
				$value = new Collection("DBItemCollection");
				foreach ($this->class as $class){
					$value[] = new DBItemCollection($class);
				}
				
				if (array_key_exists("values", $data)){
					foreach ($data["values"] as $id){
						if (
							preg_match("/^([a-z_]+)#(\\d+)$/i", $id, $matches) &&
							($classPos = array_search($matches[1], $this->class)) !== false
						){
							$value[$classPos][] = DBItem::getCLASS($matches[1], $matches[2]);
						}
					}
				}
				$translatedData[$this->name] = $value;
			}
		}
	}
	
	public function setValue(DBItem $item, $collection){
		if (is_a($collection, "Collection") && $collection->getClass() === "DBItemCollection"){
			foreach ($collection as $value){
				if (!array_key_exists($value->getClass(), $this->subFields)){
					throw new InvalidArgumentException("Property " . $this->name . " contains a not accepted class.");
				}
				$this->subFields[$value->getClass()]->setValue($item, $value);
			}
		}
		else {
			throw new InvalidArgumentException("Property " . $this->name . " is not a Colleciton of DBItemCollection.");
		}
	}

	/**
	 * {@inheritdoc}
	 *
	 * @param DBItem $item
	 * @return null
	 */
	public function getValue(DBItem $item){
		$ret = new Collection("DBItemCollection");
		foreach ($this->subFields as $subField){
			$ret[] = $subField->getValue($item);
		}
		return $ret;
	}
	
	/**
	 * {@inheritdoc}
	 * 
	 * @param DBItem $item
	 */
	public function deleteDependencies(DBItem $item){
		$col = new Collection("DBItemCollection");
		foreach ($this->class as $class){
			$col[] = new DBItemCollection($class);
		}
		$this->setValue($item, $col);
	}
}

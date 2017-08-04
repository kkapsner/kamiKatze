<?php

/**
 * DBItemFieldDBDynamicItemNToOne definition file
 */

/**
 * Description of DBItemFieldDBDynamicItemNToOne
 *
 * @author kkapsner
 */
class DBItemFieldDBDynamicItemOneToN extends DBItemFieldDBItemOneToN {
	/**
	 * {@inheritdoc}
	 * 
	 * @param string|string[] $newClass
	 */
	public function setClass($newClass){
		if (!is_array($newClass)){
			$this->class = array($newClass);
		}
		else {
			$this->class = $newClass;
		}
		$this->classSpecifier = array_map(array("DBItemClassSpecifier", "make"), $this->class);
		$this->correlationField = array();
		foreach ($this->classSpecifier as $i => $classSpecifier){
			$this->correlationField[$i] = self::parseClass($classSpecifier)->getFieldByName($this->correlationName);
		}
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
		$oldValueCollections = $this->getValue($item);
		if (is_a($collection, "Collection") && $collection->getClass() === "DBItemCollection"){
			foreach ($collection as $value){
				$classPos = array_search($value->getClass(), $this->class);
				if ($classPos === false){
					throw new InvalidArgumentException("Property " . $this->name . " contains a not accepted class.");
				}
				$oldValue = $oldValueCollections[$classPos];
				$newValue = array();

				foreach ($value as $valueItem){
					if (($pos = $oldValue->search($valueItem, true)) !== false){
						$oldValue->splice($pos, 1);
					}
					else {
						$newValue[] = $valueItem;
					}
				}
				foreach ($newValue as $valueItem){
					if ($valueItem->{$this->correlationName} !== null && !$this->canOverwriteOthers){
						throw new InvalidArgumentException("Property " . $this->name . " is overwrite protected.");
					}
					$valueItem->{$this->correlationName} = $item;
				}

				foreach ($oldValue as $valueItem){
					$valueItem->{$this->correlationName} = null;
				}
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
		$db = DB::getInstance();
		foreach ($this->classSpecifier as $i => $classSpecifier){
			$ret[] = DBItem::getByConditionCLASS(
				$classSpecifier,
				$this->correlationField[$i]->getWhere($item)
			);
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

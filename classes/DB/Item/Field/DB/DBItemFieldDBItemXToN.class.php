<?php
/**
 * DBItemFieldDBItemXToN definition file
 */

/**
 * Representation of a DBItem field
 *
 * @author Korbinian Kapsner
 * @package DB\Item\Field
 */
abstract class DBItemFieldDBItemXToN extends DBItemFieldDBItem{
	
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
		if (is_a($value, "DBItemCollection")){
			$ok = true;
			foreach ($value as $item){
				if (!is_a($item, $this->class)){
					$ok = false;
					break;
				}
			}
			return $ok;
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
				$value = new DBItemCollection($this->class);
				if (array_key_exists("values", $data)){
					foreach ($data["values"] as $id){
						$value[] = DBItem::getCLASS($this->classSpecifier, $id);
					}
				}
				$translatedData[$this->name] = $value;
			}
		}
	}

	/**
	 * {@inheritdoc}
	 * 
	 * @param DBItem $item
	 */
	protected function deleteDependencies(DBItem $item){
		$item->{$this->name} = new DBItemCollection($this->class);
	}
	/**
	 * {@inheritdoc}
	 * 
	 * @param DBItem $value
	 */
	public function getWhere($value){
		throw new Exception("Not implemented");
	}
}
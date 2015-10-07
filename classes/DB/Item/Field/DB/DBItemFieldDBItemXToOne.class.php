<?php
/**
 * DBItemFieldDBItemXToOne definition file
 */

/**
 * Representation of a DBItem field
 *
 * @author Korbinian Kapsner
 * @package DB\Item\Field
 */
abstract class DBItemFieldDBItemXToOne extends DBItemFieldDBItem{
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
		return is_a($value, $this->class);
	}

	/**
	 * {@inheritdoc}
	 *
	 * @param array $data
	 * @param array $translatedData
	 */
	public function translateRequestData($data, &$translatedData){
		if (array_key_exists($this->name, $data)){
			if ($data[$this->name]){
				$translatedData[$this->name] = DBItem::getCLASS($this->class, $data[$this->name]);
			}
			else {
				$translatedData[$this->name] = null;
			}
		}
	}

	/**
	 * {@inheritdoc}
	 * 
	 * @param DBItem $item
	 */
	protected function deleteDependencies(DBItem $item){
		$item->{$this->name} = null;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @param DBItem $item
	 * @return null
	 */
	public function getValue(DBItem $item){
		$value = $item->getRealValue($this);
		if ($value !== null){
			return DBItem::fastGetClass($this->classSpecifier, $value);
		}
		else{
			return null;
		}
	}

	/**
	 * {@inheritdoc}
	 *
	 * @param DBItem $item
	 * @param class $value
	 * @throws InvalidArgumentException
	 */
	public function setValue(DBItem $item, $value){

		$oldValue = $this->getValue($item);
		if ($value === null){
			$item->setRealValue($this->name, null);
		}
		elseif (is_a($value, $this->class)){
			if ($oldValue !== $value){
				$item->setRealValue($this->name, $value->DBid);
				if ($this->correlation === self::ONE_TO_ONE){
					$value->__set($this->correlationName, null);
					$value->setRealValue($this->correlationName, $item->DBid);
				}
			}
		}
		else {
			throw new InvalidArgumentException("Property " . $this->name . " is no " . $this->class . ".");
		}

		/** @todo better saving possible?*/
		if ($value !== null) $value->save();
		if ($oldValue !== null) $oldValue->save();
		$item->save();
	}

	/**
	 * {@inheritdoc}
	 * 
	 * @param DBItem $value
	 */
	public function getWhere($value){
		$db = DB::getInstance();
		$name = $db->quote($this->name, DB::PARAM_IDENT);
		if ($value === null){
			return $name . " IS NULL";
		}
		else {
			return $name . " = " . $db->quote($value->DBid);
		}
	}

}
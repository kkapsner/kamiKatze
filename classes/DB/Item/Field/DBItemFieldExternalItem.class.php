<?php
/**
 * DBItemFieldExternalItem definition file
 */

/**
 * Representation of a ExternalItem field
 *
 * @author Korbinian Kapsner
 * @package DB\Item\Field
 */
class DBItemFieldExternalItem extends DBItemField{

	/**
	 * If this is not null this field represents another DBItem with this class.
	 * @var string
	 */
	public $class = null;
	
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
	 * @param DBItemClassSpecifier $classSpecifier
	 * @param mixed[] $properties
	 */
	protected function adoptProperties(DBItemClassSpecifier $classSpecifier, $properties){
		parent::adoptProperties($classSpecifier, $properties);

		$this->class = array_read_key("externalClass", $properties, null);
		
		// disable default options...
		$this->searchable = false;
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
			if ($data[$this->name]){
				$translatedData[$this->name] = call_user_func(array($this->class, "getById"), $data[$this->name]);
			}
			else {
				$translatedData[$this->name] = null;
			}
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
		return $value->getId();
	}

	/**
	 * {@inheritdoc}
	 *
	 * @param DBItem $item
	 * @param array $values
	 */
	public function performAssignmentsAfterCreation(DBItem $item, $values){
//		if (array_key_exists($this->name, $values)){
//			$item->{$this->name} = $values[$this->name];
//		}
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
			return call_user_func(array($this->class, "getById"), $value);
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
		if ($value === null){
			$item->setRealValue($this->name, null);
		}
		elseif ($value instanceof $this->class){
			$item->setRealValue($this->name, $value->getId());
		}
		else {
			throw new InvalidArgumentException("Property " . $this->name . " is no " . $this->class . ".");
		}
	}

}

?>

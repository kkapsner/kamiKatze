<?php
/**
 * DBItemFieldDBItemOneToN definition file
 */

/**
 * Representation of a DBItem field with a OneToN correlation
 *
 * @author Korbinian Kapsner
 * @package DB\Item\Field
 */
class DBItemFieldDBItemOneToN extends DBItemFieldDBItemXToN{
	/**
	 * The condition that the correlated DBItems have to fulfill.
	 * @var string
	 */
	public $correlationCondition = null;
	
	/**
	 * {@inheritdoc}
	 * 
	 * @param DBItemClassSpecifier $classSpecifier
	 * @param mixed[] $properties
	 */
	protected function adoptProperties(DBItemClassSpecifier $classSpecifier, $properties){
		parent::adoptProperties($classSpecifier, $properties);
		$this->correlationCondition = array_read_key("correlationCondition", $properties, $this->correlationCondition);
		if ($this->correlationCondition){
			$this->editable = false;
		}
	}

	/**
	 * {@inheritdoc}
	 *
	 * @param DBItem $item
	 * @return null
	 */
	public function getValue(DBItem $item){
		return DBItem::getByConditionCLASS(
			$this->class,
			DB::getInstance()->quote($this->correlationName, DB::PARAM_IDENT) . " = " . $item->DBid .
			($this->correlationCondition? " AND " . $this->correlationCondition: "")
		);
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
		
		if (is_a($value, "DBItemCollection")){
			if ($value->getClass() !== $this->class && !is_subclass_of($value->getClass(), $this->class)){
				throw new InvalidArgumentException("Property " . $this->name . " contains a non " . $this->class . ".");
			}
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
		else {
			throw new InvalidArgumentException("Property " . $this->name . " is not a DBItemCollection.");
		}
	}
}
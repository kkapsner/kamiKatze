<?php
/**
 * DBItemFieldDBItemOneToOne definition file
 */

/**
 * Representation of a DBItem field with OneToOne correlation
 *
 * @author Korbinian Kapsner
 * @package DB\Item\Field
 */
class DBItemFieldDBItemOneToOne extends DBItemFieldDBItemXToOne{

	/**
	 * {@inheritdoc}
	 *
	 * @param DBItem $item
	 * @param class $value
	 * @throws InvalidArgumentException
	 */
	public function setValue(DBItem $item, $value){
		$oldValue = $this->getValue($item);
		//remove old dependency
		if ($oldValue !== $value){
			if (!$this->canOverwriteOthers && $value !== null && $value->{$this->correlationName} !== null){
				throw new InvalidArgumentException("Property " . $this->name . " is overwrite protected.");
			}
			if ($oldValue !== null){
				if ($oldValue->{$this->correlationName} === $item){
					$oldValue->setRealValue($this->correlationName, null);
				}
				else {
					$oldValue->__set($this->correlationName, null);
				}
			}
		}
		parent::setValue($item, $value);
	}

}
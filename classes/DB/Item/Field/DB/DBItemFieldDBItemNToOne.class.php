<?php
/**
 * DBItemFieldDBItemNToOne definition file
 */

/**
 * Representation of a DBItem field with NtoOne correlation
 *
 * @author Korbinian Kapsner
 * @package DB\Item\Field
 */
class DBItemFieldDBItemNToOne extends DBItemFieldDBItemXToOne{
	

	/**
	 * {@inheritdoc}
	 * 
	 * @param mixed $value
	 * @param string $nameOut
	 * @param string|null $valueOut
	 */
	public function appendDBNameAndValueForCreate($value, &$nameOut, &$valueOut = null){
		DBItemField::appendDBNameAndValueForCreate($value instanceof DBItem? $value->DBid: null, $nameOut, $valueOut);
	}

	/**
	 * {@inheritdoc}
	 *
	 * @param DBItem $item
	 * @param array $values
	 */
	public function performAssignmentsAfterCreation(DBItem $item, $values){}
}
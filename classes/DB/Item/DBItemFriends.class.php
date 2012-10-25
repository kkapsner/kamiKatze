<?php
/**
 * DBItemFriends definition file
 */

/**
 * allows some shared protected functions between DBItem and DBItemField.
 *
 * @author Korbinian Kapsner
 * @package DB\Item
 */
abstract class DBItemFriends extends ViewableHTML{
	/**
	 * Only to implement in DBItemField!
	 *
	 * @param type $id
	 * @param type $values
	 */
	protected function createDependencies($id, $values){
		throw new BadFunctionCallException("Not callable!");
	}

	/**
	 * Only to implement in DBItemField!
	 *
	 * @param DBItem $item
	 * @param array $values
	 */
	protected function performAssignmentsAfterCreation(DBItem $item, $values){
		throw new BadFunctionCallException("Not callable!");
	}

	/**
	 * Only to implement in DBItemField!
	 *
	 * @param DBItem $item
	 */
	protected function loadDependencies(DBItem $item){
		throw new BadFunctionCallException("Not callable!");
	}

	/**
	 * Only to implement in DBItemField!
	 *
	 * @param DBItem $item
	 */
	protected function saveDependencies(DBItem $item){
		throw new BadFunctionCallException("Not callable!");
	}

	/**
	 * Only to implement in DBItemField!
	 *
	 * @param DBItem $item
	 */
	protected function deleteDependencies(DBItem $item){
		throw new BadFunctionCallException("Not callable!");
	}

	/**
	 * Only to implement in DBItem!
	 *
	 * @param DBItemField $field
	 * @throws BadFunctionCallException
	 */
	protected function realValueChanged(DBItemField $field){
		throw new BadFunctionCallException("Not callable!");
	}

	/**
	 * Only to implement in DBItem!
	 *
	 * @param DBItemField $field
	 * @throws BadFunctionCallException
	 */
	protected function getRealValue(DBItemField $field){
		throw new BadFunctionCallException("Not callable!");
	}

	/**
	 * Only to implement in DBItem!
	 *
	 * @param DBItemField $field
	 * @throws BadFunctionCallException
	 */
	protected function makeRealNewValueOld(DBItemField $field){
		throw new BadFunctionCallException("Not callable!");
	}

	/**
	 * Only to implement in DBItem!
	 *
	 * @param string $name
	 * @param type $value
	 */
	protected function setRealValue($name, $value){
		throw new BadFunctionCallException("Not callable!");
	}
}

?>

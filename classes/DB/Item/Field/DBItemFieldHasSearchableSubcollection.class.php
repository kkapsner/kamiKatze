<?php
/**
 * DBItemFieldHasSubcollection definition file
 */

/**
 * Interface for a field that has sub collections.
 *
 * @author Korbinian Kapsner
 * @package DB\Item\Field
 */
interface DBItemFieldHasSearchableSubcollection{
	/**
	 * Returns the subcollection appropriate to the given DBItem.
	 *
	 * @param DBItem $item
	 * @return DBItemFieldCollection|null The appropriate collection or null if no collection is appropriate
	 */
	public function getSubcollection(DBItem $item);

	/**
	 * Returns all subcollections in the field.
	 *
	 * @return DBItemFieldCollection[]
	 */
	public function getAllSubcollections();
}

?>

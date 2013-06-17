<?php

/*
 * Declaration file of DBItemExternalClassInterface
 */

/**
 * Interface to be implemented by a class that should be used as an external class.
 * 
 * @author kkapsner
 */
interface DBItemExternalClassInterface{
	/**
	 * Method to get an item by its ID.
	 * @param int $id the ID of the item
	 * @return mixed Returns the desired item with ID $id on success or null on failure.
	 */
	public static function getById($id);
	
	/**
	 * Getter for all available items.
	 * @return mixed[] Returns all items that are available.
	 */
	public static function getAll();
	
	/**
	 * Getter for the items ID.
	 * @return int Returns the ID of the item.
	 */
	public function getId();
}

?>

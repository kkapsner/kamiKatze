<?php

/**
 * DBItemFieldFileItem definition file
 */

/**
 * Wrapper class for a file which is referenced in a DBItem.
 *
 * @author Korbinian Kapsner
 * @package DB\Item\Field
 */
class DBItemFieldFileItem extends DBItem{
	/**
	 * {@inheritdoc}
	 * 
	 * Deletes the file in the file system additionally.
	 */
	public function delete(){
		unlink($this->path);
		parent::delete();
	}
}

?>

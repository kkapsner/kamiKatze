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
	
	/**
	 * Getter for the files path.
	 * @return String Returns the path to the file.
	 */
	public function getPath(){
		return DBItemFieldFile::$fileFolder . $this->subpath;
	}
	
	/**
	 * Getter for the files URL.
	 * @return String Returns the URL to the file.
	 */
	public function getURL(){
		return DBItemFieldFile::$urlToFileFolder . $this->subpath;
	}
}

?>

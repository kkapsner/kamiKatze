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
	 * Renames the file. If the new filename already exists
	 * 
	 * @param String $newName the new file name
	 */
	public function rename($newName){
		if ($newName && $newName !== $this->subpath){
			$oldPath = $this->path;
			$dir = dirname($oldPath) . "/";
			
			if (file_exists($dir . $newName)){
				$c = 0;
				while (file_exists($dir . $c . "/" . $newName)){
					$c++;
				}
				if (!file_exists($dir . $c . "/")){
					mkdir($dir . $c);
				}
				$newName = $c . "/" . $newName;
			}
			else {
				$c = false;
			}
			
			if (rename($oldPath, $dir . $newName)){
				$this->subpath = dirname($this->subpath) . "/" .
					(($c !== false)? "": $c . "/") .
					$newName;
			}
		}
	}
	
	/**
	 * Getter for the files name
	 * 
	 * @return String Returns the filename
	 */
	public function getFilename(){
		return basename($this->subpath);
	}
	
	/**
	 * Getter for the files path.
	 * 
	 * @return String Returns the path to the file.
	 */
	public function getPath(){
		return DBItemFieldFile::$fileFolder . $this->subpath;
	}
	
	/**
	 * Getter for the files URL.
	 * 
	 * @return String Returns the URL to the file.
	 */
	public function getURL(){
		return DBItemFieldFile::$urlToFileFolder . $this->subpath;
	}
}

?>
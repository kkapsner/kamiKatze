<?php
/**
 * DBItemFieldFile definition file
 */

/**
 * Description of DBItemFieldFile
 *
 * @author Korbinian Kapsner
 * @package DB\Item\Field
 */
class DBItemFieldFile extends DBItemField{
	/**
	 * The folder where the uploaded files should be stored.
	 * @var string
	 */
	public static $fileFolder = "./files/";
	/**
	 * The URL to the folder where the uploaded files are stored.
	 * @var string
	 */
	public static $urlToFileFolder = "./files/";

	/**
	 * Flag if the file should be displayed as an image
	 * @var boolean
	 */
	public $image = false;
	/**
	 * The class specifier for the file item.
	 * @var DBItemClassSpecifier
	 */
	public $specifier;
	/**
	 * {@inheritdoc}
	 * 
	 * @param DBItemClassSpecifier $classSpecifier
	 * @param mixed[] $options
	 */
	protected function parseOptions(DBItemClassSpecifier $classSpecifier, $options){
		parent::parseOptions($classSpecifier, $options);
		$this->specifier = new DBItemClassSpecifier(
			"DBItemFieldFileItem",
			DBItemClassSpecifier::$tablePrefix . array_read_key("fileTable", $options, "Files")
		);
		$this->image = array_read_key("image", $options, $this->image);
	}

	/**
	 * {@inheritdoc}
	 * 
	 * @param DBItem $item
	 */
	protected function deleteDependencies(DBItem $item){
		$fileItem = $this->getValue($item);
		if ($fileItem !== null){
			$fileItem->delete();
		}
	}

	/**
	 * {@inheritdoc}
	 * 
	 * @param DBItem $item
	 * @return DBItemFieldFileItem
	 */
	public function getValue(DBItem $item){
		if ($item === null){
			return null;
		}
		else {
			return DBItem::getCLASS($this->specifier, $item->getRealValue($this));
		}
	}

	/**
	 * {@inheritdoc}
	 *
	 * @param type $value
	 * @return type
	 * @todo implement
	 */
	public function isValidValue($value){
		return parent::isValidValue($value);
	}

	/**
	 * {@inheritdoc}
	 *
	 * Exspects a DBItemFieldFileItem.
	 * @param DBItem $item
	 * @param DBItemFieldFileItem $value
	 */
	public function setValue(DBItem $item, $value){
		$oldValue = $this->getValue($item);
		if ($oldValue !== null){
			$oldValue->delete();
		}
		if ($value === null){
			parent::setValue($item, $value);
		}
		elseif ($value instanceof DBItemFieldFileItem){
			parent::setValue($item, $value->DBid);
		}
		else {
			
		}
	}

	/**
	 * {@inheritdoc}
	 * 
	 * @param mixed[] $data
	 * @param mixed[] $translatedData
	 */
	public function translateRequestData($data, &$translatedData){
		if (
			array_key_exists($this->name, $data) &&
			is_array($data[$this->name]) &&
			array_key_exists("path", $data[$this->name])
		){
			$arrayPath = json_decode($data[$this->name]["path"]);
			if (is_array($arrayPath) && ($info = $this->getFileInfo($arrayPath)) !== null && $info["error"] === 0){
				$filename = str_replace("..", ".", $info["name"]);
				if (file_exists(self::$fileFolder . $filename)){
					$c = 0;
					while (file_exists(self::$fileFolder . $c . "/" . $filename)){
						$c++;
					}
					if (!file_exists(self::$fileFolder . $c . "/")){
						mkdir(self::$fileFolder . $c);
					}
					$filename = $c . "/" . $filename;
				}
				$path = realpath(self::$fileFolder) . DIRECTORY_SEPARATOR . $filename;
				if (move_uploaded_file($info["tmp_name"], $path)){
					$fileItem = DBItem::createCLASS($this->specifier, array(
						"subpath" => $filename
						#"URL" => self::$urlToFileFolder . $filename,
						#"path" => $path
					), true);
					$translatedData[$this->name] = $fileItem;
				}
			}
			elseif (
				array_key_exists("fileID", $data[$this->name]) &&
				array_key_exists("filename", $data[$this->name])
			){
//				try {
					$fileItem = DBItem::getCLASS($this->specifier, $data[$this->name]["fileID"]);
					$filename = $data[$this->name]["filename"];
					if ($fileItem->filename !== $filename){
						if ($filename){
							$fileItem->rename($filename);
						}
						else {
							$translatedData[$this->name] = null;
						}
					}
//				}
//				catch (Exception $e){}
			}
		}
	}

	/**
	 * {@inheritdoc}
	 * 
	 * @param type $value
	 * @return type
	 */
	public function translateToDB($value){
		return $value->DBid;
	}

	
	/**
	 * Returns the file info for a given upload "path".
	 *
	 * @param type $arrayPath
	 * @return string[]|null
	 */
	private function getFileInfo($arrayPath){
		$firstLayer = $_FILES[array_shift($arrayPath)];
		$info = array(
			"name" => $firstLayer["name"],
			"type" => $firstLayer["type"],
			"size" => $firstLayer["size"],
			"tmp_name" => $firstLayer["tmp_name"],
			"error" => $firstLayer["error"]
		);
		foreach ($arrayPath as $pathPart){
			foreach ($info as $name => $value){
				if (!array_key_exists($pathPart, $value)){
					return null;
				}
				else {
					$info[$name] = $value[$pathPart];
				}
			}
		}
		return $info;
	}
}

?>

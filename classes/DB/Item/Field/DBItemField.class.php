<?php
/**
 * DBItemField class definition
 */

/**
 * Options and properties managing class for a field.
 *
 * @author Korbinian Kapsner
 * @package DB\Item\Field
 */
class DBItemField extends DBItemFriends{
	const DB_ITEM = "DBItem";
	const EXTERNAL_ITEM = "externalItem";

	/**
	 * Cache array for already parsed classes.
	 * @var array
	 */
	static protected $classOptions = array();

	/**
	 * Name of the field.
	 * @var string
	 */
	public $name;
	/**
	 * Name to be displayed.
	 * @var string
	 */
	public $displayName;
	/**
	 * Regular expression that a value must match.
	 * @var string
	 */
	public $regExp = null;
	/**
	 * The type of the field
	 * @var string
	 */
	public $type = null;
	/**
	 * The type extension (part in the brackets). If the field is an enum or set ths is an array of the possible values.
	 * @var mixed
	 */
	public $typeExtension = null;
	/**
	 * If null is a valid value.
	 * @var boolean
	 */
	public $null = false;
	/**
	 * The default value.
	 * @var mixed
	 */
	public $default = null;
	/**
	 * If this field should be displayed in output.
	 * @var boolean
	 */
	public $displayable = true;
	/**
	 * If a search can be performed on this field.
	 * @var boolean
	 */
	public $searchable = true;
	/**
	 * If this field can be changed.
	 * @var boolean
	 */
	public $editable = true;

	/**
	 * If there is some field chaining - this is the parent field.
	 * @var DBItemField
	 */
	protected $parentField = null;


	/**
	 * Generates the field options from a DB result.
	 * @param string $classSpecifier The class specifier
	 * @param array $result The results to parse
	 * @return self
	 */
	protected static function parseResult($classSpecifier, $result){
		if (preg_match('/^(.+?)\((.*)\)$/', $result["Type"], $m)){
			$type = $m[1];
			$typeExtension = $m[2];
			
			if ($type === "enum" || $type === "set"){
				if (preg_match_all('/\'((?:\'{2}|[^\'])*)\'/', $typeExtension, $m)){
					$typeExtension = $m[1];
					foreach ($typeExtension as $i => $v){
						$typeExtension[$i] = str_replace("''", "'", $v);
					}
				}
				else {
					$typeExtension = array();
				}
			}
		}
		else {
			$type = $result['Type'];
			$typeExtension = null;
		}
		
		$options = json_decode($result["Comment"], true);
		if (!is_array($options)){
			$options = array();
		}
		if (array_key_exists("customField", $options)){
			$type = "custom";
		}
		elseif (array_key_exists("class", $options)){
			$type = self::DB_ITEM;
		}
		elseif (array_key_exists("externalClass", $options)){
			$type = self::EXTERNAL_ITEM;
		}
		elseif (array_read_key("computedValue", $options, false)){
			$type = "computedValue";
		}
		elseif (array_read_key("isArray", $options, false) || array_read_key("array", $options, false)){
			$type = "array";
		}
		elseif (array_read_key("isFile", $options, false) || array_read_key("file", $options, false)){
			$type = "file";
		}
		elseif (array_read_key("isLink", $options, false) || array_read_key("link", $options, false)){
			$type = "link";
		}
		elseif ($type === "enum" && array_read_key("extender", $options, false)){
			$type = "extender";
		}
		
		switch ($type){
			case "custom":
				$item = new $options["customField"]($result["Field"]);
				break;
			case "array":
				$item = new DBItemFieldArray($result["Field"]);
				break;
			case "enum":
				$item = new DBItemFieldEnum($result["Field"]);
				break;
			case "extender":
				$item = new DBItemFieldExtender($result["Field"]);
				break;
			case "file":
				$item = new DBItemFieldFile($result["Field"]);
				break;
			case "set":
				$item = new DBItemFieldSet($result["Field"]);
				break;
			case "tinytext":
			case "text":
			case "mediumtext":
			case "longtext":
				$item = new DBItemFieldText($result["Field"]);
				break;
			case "link":
				$item = new DBItemFieldLink($result["Field"]);
				break;
			case self::DB_ITEM:
				$item = new DBItemFieldDBItem($result["Field"]);
				break;
			case self::EXTERNAL_ITEM:
				$item = new DBItemFieldExternalItem($result["Field"]);
				break;
			case "computedValue":
				$item = new DBItemFieldComputedValue($result["Field"]);
				break;
			case "tinyint":
				if ($typeExtension == 1){
					$item = new DBItemFieldBoolean($result["Field"]);
					break;
				}
			default:
				$item = new DBItemFieldNative($result["Field"]);
		}
		
		
		$item->null = $result['Null'] === "YES";
		$item->default = $result["Default"];
		$item->type = $type;
		$item->typeExtension = $typeExtension;
		$item->displayName = array_read_key("DisplayName", $result, $item->displayName);
		
		$item->parseOptions($classSpecifier, $options);
		return $item;
	}
	
	/**
	 * Generates the field options from a group of DB results.
	 * @param string $classSpecifier The class specifier
	 * @param array $group The array of results to parse
	 * @return slef
	 */
	protected static function parseGroupResults($classSpecifier, $groupName, $group){
		$options = array();
		foreach ($group as $result){
			$newOptions = json_decode($result["Comment"], true);
			if (is_array($newOptions)){
				$options = array_merge($options, $newOptions);
			}
		}
//		if (array_key_exists("groupFieldClass", $options)){
//			$item = new $options["groupFieldClass"]($groupName);
//			$item->parseGroup($classSpecifier, $group);
//			$item->parseOptions($classSpecifier, $options);
//		}
//		else {
			$item = new DBItemFieldGroup($groupName);
			$item->parseGroup($classSpecifier, $group);
//		}
		return $item;
	}

	/**
	 * Performs the iterator for parseClass.
	 * @param Iterator $iter
	 * @param DBItemClassSpecifier $classSpecifier
	 * @return DBItemFieldCollection
	 */
	protected static function iterateForParseClass($classSpecifier, $iter){
		$ret = new DBItemFieldCollection();
		$groups = array();

		foreach ($iter as $result){
			$name = $result["Field"];
			if ($name === "id"){
				continue;
			}
			if (($pos = strpos($name, ">", array_read_key("groupNameOffset", $result, 0))) !== false){
				$groupName = substr($name, 0, $pos);
				$result["groupNameOffset"] = $pos + 1;
				$result["DisplayName"] = substr($name, $pos + 1);
				
				if (!array_key_exists($groupName, $groups)){
					$groups[$groupName] = array(
						"index" => count($ret),
						"columns" => array()
					);
					$ret[] = new DBItemField("");
				}
				
				$groups[$groupName]["columns"][] = $result;
			}
			else {
				$ret[] = self::parseResult($classSpecifier, $result);
			}
		}
		foreach ($groups as $groupName => $group){
			$ret[$group["index"]] = self::parseGroupResults($classSpecifier, $groupName, $group["columns"]);
		}
		return $ret;
	}

	/**
	 * Gets the field options for a specific class.
	 *
	 * @param string $classSpecifier The class name
	 * @return DBItemFieldCollection
	 */
	public static function parseClass($classSpecifier){
		$classSpecifier = DBItemClassSpecifier::make($classSpecifier);
		$specifiedName = $classSpecifier->getSpecifiedName();

		if (!array_key_exists($specifiedName, self::$classOptions)){
			$db = DB::getInstance();
			self::$classOptions[$specifiedName] = self::iterateForParseClass(
				$classSpecifier,
				$db->query("SHOW FULL COLUMNS FROM " . $db->quote($classSpecifier->getTableName(), DB::PARAM_IDENT))
			);
		}
		return self::$classOptions[$specifiedName];
	}

	/**
	 * Protected constructor of DBItemFieldOption.
	 *
	 * @param type $name
	 */
	protected function __construct($name){
		$this->name = $name;
		$this->displayName = $name;
	}

	/**
	 * Parses the field options according to its type.
	 * 
	 * Overwrite this function for the specific types.
	 * @param DBItemClassSpecifier $classSpecifier
	 * @param array $options
	 */
	protected function parseOptions(DBItemClassSpecifier $classSpecifier, $options){
		$this->regExp = array_read_key("regExp", $options, null);
		$this->displayable = array_read_key("displayable", $options, $this->displayable);
		$this->editable = array_read_key("editable", $options, $this->editable);
		$this->searchable = array_read_key("searchable", $options, $this->searchable);
		$this->displayName = array_read_key("displayName", $options, $this->displayName);
	}

	/**
	 * Validates translated data.
	 *
	 * @param mixed[] $values
	 * @return DBItemValidationException[] Returns an array of occured errors. If this array is empty no error occured.
	 */
	public function validate($values){
		$errors = array();
		if (array_key_exists($this->name, $values)){
			$value = $values[$this->name];
			if ($value === null && !$this->null){
				$errors[] = new DBItemValidationException(
					"Field " . $this->displayName . " may not be NULL.",
					DBItemValidationException::WRONG_NULL,
					$this
				);
			}
			if ($this->regExp && !preg_match($this->regExp, $value)){
				$errors[] = new DBItemValidationException(
					"Field " . $this->displayName . " must match regular expression " . $this->regExp . " but '" . $value . "' provided.",
					DBItemValidationException::WRONG_REGEXP,
					$this
				);
			}
		}
		elseif ($this->editable && $this->default === null && !$this->null) {
			$errors[] = new DBItemValidationException(
				"Field " . $this->displayName . " is reqired.",
				DBItemValidationException::WRONG_MISSING,
				$this
			);
		}
		return $errors;
	}

	/**
	 * Checks if the $value is correct for the specific field.
	 * @param mixed $value
	 * @return boolean
	 */
	public function isValidValue($value){
		if ($value === null){
			return $this->null;
		}
		if ($this->regExp && !preg_match($this->regExp, $value)){
			return false;
		}
		return true;
	}

	/**
	 * Translates a request data array to a data array with values to be assigned to a DBItem.
	 *
	 * To not call this - call {@see DBItemCollection::translateRequestData()} instead.
	 * @param array $data
	 * @param array $translatedData
	 */
	public function translateRequestData($data, &$translatedData){
		if (array_key_exists($this->name, $data)){
			$value = $data[$this->name];
			if ($value === "" && $this->null){
				$value = null;
			}
			$translatedData[$this->name] = $value;
		}
	}

	/**
	 * Generates the field name for the post request.
	 * 
	 * @param DBItem $dbItem
	 * @param boolean $nameArray if the name cascade should be returned as an array. If not a string is returned.
	 * @return string|string[]
	 */
	protected function getPostName(DBItem $dbItem, $nameArray = false){
		if ($this->parentField !== null){
			if ($nameArray){
				$arr = $this->parentField->getPostName($dbItem, $nameArray);
				$arr[] = $this->name;
				return $arr;
			}
			else {
				return $this->parentField->getPostName($dbItem, $nameArray) . $this->html("[" . $this->name . "]");
			}
		}
		else {
			if ($nameArray){
				return array(get_class($dbItem), $dbItem->DBid, $this->name);
			}
			else {
				return $this->html(get_class($dbItem) . "[" . $dbItem->DBid . "][" . $this->name . "]");
			}
		}
	}

	/**
	 * Translates a value to the representation appropriate for the DB.
	 *
	 * @param type $value
	 * @return string|null If null is returned the field has no value to be stored in the original table.
	 */
	public function translateToDB($value){
		if ($value === null && $this->null){
			return "NULL";
		}
		else {
			return DB::getInstance()->quote($value, DB::PARAM_STR);
		}
	}
	
	/**
	 * Translates the name to the representaiton appropriate for the DB.
	 * @return string|null if null is returned the field has no value to be stored in the original table.
	 */
	public function translateNameToDB(){
		return DB::getInstance()->quote($this->name, DB::PARAM_IDENT);
	}
	
	/**
	 * Appends the DB query strings to the input strings $nameOut and $vlaueOut.
	 * @param mixed $value
	 * @param string $nameOut
	 * @param string|null $valueOut (optional) if this parameter is null the 
	 *        "name = value" string is appended to $nameOut.
	 */
	public function appendDBNameAndValueForCreate($value, &$nameOut, &$valueOut = null){
		$trValue = $this->translateToDB($value);
		if ($trValue !== null){
			if ($valueOut === null){
				$nameOut .= ($nameOut? ",": "") . $this->translateNameToDB() . "=" . $trValue;
			}
			else {
				$nameOut .= ($nameOut? ",": "") . $this->translateNameToDB();
				$valueOut .= ($valueOut? ",": "") . $trValue;
			}
		}
	}
	
	/**
	 * 
	 * @param mixed $value
	 * @param string $propsOut
	 */
	public function appendDBNameAndValueForUpdate($value, &$propsOut){
		$this->appendDBNameAndValueForCreate($value, $propsOut);
	}

	/**
	 * Creates all depedencies of the item in other tables than the original one.
	 *
	 * @param int $id
	 * @param array $values
	 */
	protected function createDependencies($id, $values){}

	/**
	 * Performs assigments that have to occure after the creation of an item.
	 * @param DBItem $item
	 * @param type $values
	 */
	protected function performAssignmentsAfterCreation(DBItem $item, $values){}

	/**
	 * Loads all depedencies of the item in other tables than the original one.
	 *
	 * @param DBItem $item
	 */
	protected function loadDependencies(DBItem $item){}

	/**
	 * Saves all depedencies of the item in other tables than the original one.
	 *
	 * @param DBItem $item
	 * @return boolean If the fields value must be stored in the original table.
	 */
	protected function saveDependencies(DBItem $item){
		return true;
	}

	/**
	 * Deletes all depedencies of the item in other tables than the original one.
	 *
	 * @param DBItem $item
	 */
	protected function deleteDependencies(DBItem $item){}

	/**
	 * Returns the value of this field in the item.
	 *
	 * @param DBItem $item
	 * @return mixed
	 */
	public function getValue(DBItem $item){
		return $item->getRealValue($this);
	}

	/**
	 * Sets the value of this field in the item.
	 *
	 * @param DBItem $item
	 * @param mixed $value
	 */
	public function setValue(DBItem $item, $value){
		$item->setRealValue($this->name, $value);
	}
}

?>
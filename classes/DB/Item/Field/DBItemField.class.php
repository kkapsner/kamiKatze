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
class DBItemField extends DBItemFriends implements DBItemFieldInterface{
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
	 * The type extension (part in the brackets). If the field is an enum or set
	 * this is an array of the possible values.
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
	 * If this field should be output in JSON.
	 * @var boolean
	 */
	public $jsonable = true;
	/**
	 * Name of an additional validation method on the class to be called for
	 * validation.
	 * @var string
	 */
	public $additionalValidation = false;

	/**
	 * If there is some field chaining - this is the parent field.
	 * @var DBItemField
	 */
	protected $parentField = null;
	
	/**
	 * The class specifier of the class that has the field.
	 * @var DBItemClassSpecifier
	 */
	protected $parentClassSpecifier = null;
	
	/**
	 * The database object to use with the field
	 * @var DB
	 */
	private $db = null;

	/**
	 * Returns the properties of a filed providing the properties of the field
	 * in the DB.
	 * 
	 * @param string[] $dbProperties
	 * @return mixed[] the parsed properties
	 */
	protected static function getProperties($dbProperties){
		$properties = json_decode($dbProperties["Comment"], true);
		if (!is_array($properties)){
			$properties = array();
		}
		$properties["rawDBOptions"] = $dbProperties;
		$properties["name"] = $dbProperties["Field"];
		
		if (preg_match('/^(.+?)\((.*)\)$/', $dbProperties["Type"], $m)){
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
			$type = $dbProperties['Type'];
			$typeExtension = null;
		}
		$properties["typeExtension"] = $typeExtension;
		
		$properties["originalType"] = $type;
		
		$properties["null"] = $dbProperties["Null"] === "YES";
		if (
			!array_key_exists("default", $properties) &&
			array_key_exists("Default", $dbProperties)
		){
			$properties["default"] = $dbProperties["Default"];
		}
		
		if (
			!array_key_exists("displayName", $properties) &&
			array_key_exists("DisplayName", $dbProperties)
		){
			$properties["displayName"] = $dbProperties["DisplayName"];
		}
		
		$properties["type"] = self::getType($properties, $type);
		
		return $properties;
	}
	
	/**
	 * Returns the desired type for a field providing the properties and the
	 * original type in the database.
	 * 
	 * @param array $properties The properties of the field.
	 * @param string $type The type of the field in the DB
	 * @return string The proper type to use.
	 */
	protected static function getType(array $properties, $type){
		if (array_key_exists("customField", $properties)){
			$type = "custom";
		}
		elseif (array_key_exists("class", $properties)){
			$type = "DBItem";
		}
		elseif (array_key_exists("externalClass", $properties)){
			$type = "externalItem";
		}
		elseif (array_read_key("computedValue", $properties, false)){
			$type = "computedValue";
		}
		elseif (array_read_key("isEnum", $properties, false) || array_read_key("enum", $properties, false)){
			$type = "referenceEnum";
			if (array_read_key("extender", $properties, false) || array_read_key("isExtender", $properties, false)){
				$type = "referenceExtender";
			}
		}
		elseif (array_read_key("isArray", $properties, false) || array_read_key("array", $properties, false)){
			$type = "array";
		}
		elseif (array_read_key("isFile", $properties, false) || array_read_key("file", $properties, false)){
			$type = "file";
		}
		elseif (array_read_key("isLink", $properties, false) || array_read_key("link", $properties, false)){
			$type = "link";
		}
		elseif ($type === "enum" && 
			(array_read_key("extender", $properties, false) || array_read_key("isExtender", $properties, false))
		){
			$type = "extender";
		}
		elseif ($type === "tinyint" && $properties["typeExtension"] == 1) {
			$type = "boolean";
		}
		elseif (preg_match ("/(?:tiny||medium|long)text$/i", $type)){
			$type = "text";
		}
		elseif (preg_match ("/(?:(?:tiny|small|medium||big)int|float|double|decimal)$/i", $type)){
			$type = "number";
		}
		
		return $type;
	}
	
	/**
	 * Generates the field from a DB result.
	 * 
	 * @param string $classSpecifier The class specifier
	 * @param array $result The results to parse
	 * @return self
	 */
	protected static function parseResult($classSpecifier, $result){
		$options = self::getProperties($result);
		
		$className = $options["type"] === "custom"?
			$options["customField"]:
			"DBItemField" . ucfirst($options["type"]);
		if (is_a($className, "DBItemFieldInterface", true)){
			return $className::create($classSpecifier, $options);
		}
		else {
			return DBItemFieldNative::create($classSpecifier, $options);
		}
	}
	
	/**
	 * Generates the field options from a group of DB results.
	 * @param string $classSpecifier The class specifier
	 * @param array $group The array of results to parse
	 * @return slef
	 */
	protected static function parseGroupResults($classSpecifier, $groupName, $group){
		$properties = array();
		foreach ($group as $result){
			$newOptions = json_decode($result["Comment"], true);
			if (is_array($newOptions)){
				$properties = array_merge($properties, $newOptions);
			}
		}
		$properties["group"] = $group;
		$properties["name"] = $groupName;
		if (
			array_key_exists("groupFieldClass", $properties) &&
			is_a($properties["groupFieldClass"], "DBItemFieldGroupInterface", true)
		){
			return $properties["groupFieldClass"]::create($classSpecifier, $properties);
		}
		else {
			return DBItemFieldGroup::create($classSpecifier, $properties);
		}
	}

	/**
	 * Performs the iterator for parseClass.
	 * @param DBItemClassSpecifier $classSpecifier
	 * @param Iterator $iter
	 * @return DBItemFieldCollection
	 */
	protected static function iterateForParseClass($classSpecifier, $iter){
		$ret = !is_array($iter)?
			self::$classOptions[$classSpecifier->getSpecifiedName()]:
			new DBItemFieldCollection();
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
			$db = DBItem::getDBCLASS($classSpecifier);
			self::$classOptions[$specifiedName] = new DBItemFieldCollection();
			self::iterateForParseClass(
				$classSpecifier,
				$db->query("SHOW FULL COLUMNS FROM " . $db->quote($classSpecifier->getTableName(), DB::PARAM_IDENT))
			);
		}
		return self::$classOptions[$specifiedName];
	}

	/**
	 * Protected constructor of DBItemField. DO NOT USE!
	 *
	 * @param type $name
	 */
	protected function __construct($name){
		$this->name = $name;
		$this->displayName = $name;
	}
	
	/**
	 * Factory function to create fields.
	 * 
	 * If this function is overwritten do not forget do add a call to
	 * $item->adoptProperties()
	 * 
	 * @param DBItemClassSpecifier $classSpecifier
	 * @param mixed[] $properties
	 * @return DBItemField
	 */
	protected static function create(DBItemClassSpecifier $classSpecifier, $properties){
		$item = new static($properties["name"]);
		$item->adoptProperties($classSpecifier, $properties);
		return $item;
	}

	/**
	 * Parses the field properties according to its type.
	 * Overwrite this function for the specific types.
	 * 
	 * IMPORTANT: This functions has to be called if overwriten.
	 * @param DBItemClassSpecifier $classSpecifier
	 * @param array $properties
	 */
	protected function adoptProperties(DBItemClassSpecifier $classSpecifier, $properties){
		$this->parentClassSpecifier = $classSpecifier;
		
		$props = array(
			"null", "default", "type", "typeExtension", "displayName", "regExp",
			"displayable", "editable", "searchable", "jsonable", "displayName", "additionalValidation"
		);
		foreach ($props as $prop){
			$this->{$prop} = array_read_key($prop, $properties, $this->{$prop});
		}
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
			if ($this->additionalValidation){
				$class = $this->parentClassSpecifier->getClassName();
				$additionalErrors = $class::{$this->additionalValidation}($this, $value);
				if (is_array($additionalErrors)){
					$errors = array_merge($errors, $additionalErrors);
				}
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
	 * Retrieves the DB for the field to use.
	 * @return DB
	 */
	protected function getDB(){
		if (!($this->db instanceof DB)){
			$this->db = DBItem::getDBCLASS($this->parentClassSpecifier);
		}
		
		return $this->db;
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
			return $this->getDB()->quote($value, DB::PARAM_STR);
		}
	}
	
	/**
	 * Translates the name to the representaiton appropriate for the DB.
	 * @return string|null if null is returned the field has no value to be stored in the original table.
	 */
	public function translateNameToDB(){
		return $this->getDB()->quote($this->name, DB::PARAM_IDENT);
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
		$trValue = $this->translateToDB($value);
		if ($trValue !== null){
			$propsOut .= ($propsOut? ",": "") . $this->translateNameToDB() . "=" . $trValue;
		}
	}

	/**
	 * Creates all depedencies of the item in other tables than the original one.
	 *
	 * @param int $id
	 * @param array $values
	 */
	public function createDependencies($id, $values){}

	/**
	 * Performs assigments that have to occure after the creation of an item.
	 * @param DBItem $item
	 * @param type $values
	 */
	public function performAssignmentsAfterCreation(DBItem $item, $values){}

	/**
	 * Loads all depedencies of the item in other tables than the original one.
	 *
	 * @param DBItem $item
	 */
	public function loadDependencies(DBItem $item){}

	/**
	 * Saves all depedencies of the item in other tables than the original one.
	 *
	 * @param DBItem $item
	 * @return boolean If the fields value must be stored in the original table.
	 */
	public function saveDependencies(DBItem $item){
		return true;
	}

	/**
	 * Deletes all depedencies of the item in other tables than the original one.
	 *
	 * @param DBItem $item
	 */
	public function deleteDependencies(DBItem $item){}

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
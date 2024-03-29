<?php
/**
 * DBItem class definition
 */

/**
 * A DBItem instance represents an entry in a DB. To specifiy the table extend this class in a class that has the name of the table.
 *
 * @author Korbinian Kapsner
 * @package DB\Item
 */
abstract class DBItem extends DBItemFriends implements JsonSerializable{
	/**
	 * Default order for the items if selected by @see DBItem::getByCondition() without third parameter.
	 * @var String
	 */
	public static $defaultOrder = false;
	
	/**
	 * Stores all instances of ANY DBItem.
	 * @var DBItem[][]
	 */
	private static $instances = array();
	
	/**
	 * Instance of the DB
	 * @var DB
	 */
	protected $db;

	/**
	 * Table name of the items class
	 * @var string
	 */
	protected $table;

	/**
	 * The class specifier.
	 * @var DBItemClassSpecifier
	 */
	protected $specifier;
	
	/**
	 * The collection of all fields.
	 * @var DBItemFieldCollection
	 */
	protected $fields;
	
	/**
	 * ID of the item in the DB
	 * @var int
	 */
	protected $DBid = null;
	
	/**
	 * If the item has changed
	 * @var bool
	 */
	protected $changed = false;

	/**
	 * If the item can be changed
	 * @var bool
	 */
	protected $changeable = true;

	/**
	 * If the item has been deleted
	 * @var bool
	 */
	protected $deleted = false;

	/**
	 * Array of the old values in the DB
	 * @var mixed[]
	 */
	protected $oldValues = array();

	/**
	 * Array of the new values that are set
	 * @var mixed[]
	 */
	protected $newValues = array();
	
	/**
	 * Cache for already processed values.
	 * @var mixed[]
	 */
	protected $processedValueCache = array();
	
	// static class functions
	
	public static function getDBCLASS($classSpecifier){
		$classSpecifier = DBItemClassSpecifier::make($classSpecifier);
		$className = $classSpecifier->getClassName();
		if (
			property_exists($className, "classDb") &&
			$className::$classDb instanceof DB
		){
			return $className::$classDb;
		}
		else {
			return DB::getInstance();
		}
	}
	
	/**
	 * Get the DBItem of type $class with ID $id.
	 * @param string|DBItemClassSpecifier $classSpecifier
	 * @param int $id
	 * @return DBItem of type $class
	 */
	public static function getCLASS($classSpecifier, $id){
		#check for valid ID
		if (!is_int($id) && !ctype_digit($id)){
			return null;
		}
		
		$classSpecifier = DBItemClassSpecifier::make($classSpecifier);
		$className = $classSpecifier->getClassName();

		#check if $class is an DBItem
		if (!is_subclass_of($className, "DBItem")){
			return null;
		}
		
		return self::fastGetCLASS($classSpecifier, (int) $id);
	}
	
	public static function fastGetCLASS(DBItemClassSpecifier $classSpecifier, $id, $data = null){
		if (!is_int($id) && !ctype_digit($id)){
			return null;
		}
		$specifiedName = $classSpecifier->getSpecifiedName();
		$className = $classSpecifier->getClassName();
		if (!array_key_exists($specifiedName, self::$instances)){
			self::$instances[$specifiedName] = array();
		}
		if (!array_key_exists($id, self::$instances[$specifiedName])){
			self::$instances[$specifiedName][$id] = new $className($classSpecifier, $id, $data);
		}

		return self::$instances[$specifiedName][$id];
	}
	
	public static function getByValueCLASS($classSpecifier, $name, $value){
		$classSpecifier = DBItemClassSpecifier::make($classSpecifier);
		
		
		$field = DBItemField::parseClass($classSpecifier)->getFieldByName($name);
		
		if ($field instanceof DBItemFieldSearchable){
			return self::getByConditionCLASS($classSpecifier, $field->getWhere($value));
		}
		else {
			throw new Exception($name . " field can not be searched.");
		}
		
	}

	/**
	 * Returns items of the $class which forfill the $where condition. 
	 * @param string|DBItemClassSpecifier $classSpecifier
	 * @param string $where
	 * @param string $orderBy
	 * @return DBItemCollection with $class
	 */
	public static function getByConditionCLASS($classSpecifier, $where = false, $orderBy = false){
		$classSpecifier = DBItemClassSpecifier::make($classSpecifier);

		$ret = new DBItemCollection($classSpecifier->getClassName());
		
		$db = self::getDBCLASS($classSpecifier);
		
		$sql = "SELECT * FROM " . $db->quote($classSpecifier->getTableName(), DB::PARAM_IDENT);
		if ($where){
			$sql .= " WHERE " . $where;
		}
		if ($orderBy){
			$sql .= " ORDER BY " . $orderBy;
		}
		else {
			$classVars = get_class_vars($classSpecifier->getClassName());
			if (array_read_key("defaultOrder", $classVars, false)){
				$sql .= " ORDER BY " . $classVars["defaultOrder"];
			}
		}
		$res = $db->query($sql);
		if ($res){
			foreach ($res as $row){
				$ret[] = self::fastGetCLASS($classSpecifier, $row["id"], $row);
			}
		}
		return $ret;
	}
	
	/**
	 * Checks if the provided $values are valid for the specific $class
	 * @param string|DBItemClassSpecifier $classSpecifier The class to be checked.
	 * @param mixed[] $values The provided values.
	 * @return true|DBItemValidationException[] true if everything is fine or an array of all occuring errors.
	 */
	public static function validateFieldsCLASS($classSpecifier, $values){
		$errors = DBItemField::parseClass($classSpecifier)->validate($values);
		if (count($errors) == 0){
			return true;
		}
		else {
			return $errors;
		}
	}

	/**
	 * Creates a new instance of the specific $classSpecifier with the values in the $fields
	 * @param string $classSpecifier Classname of the new instance
	 * @param mixed[] $fieldValues field values
	 * @param bool $bypassValidation If set to true the provided values are not validated.
	 * DO ONLY USE IF VALIDATION IS DONE BEFOREHAND MANUALY.
	 * @return DBItem of type $class 
	 */
	public static function createCLASS($classSpecifier, $fieldValues = array(), $bypassValidation = false){
		$classSpecifier = DBItemClassSpecifier::make($classSpecifier);

		if (!$bypassValidation){
			$errors = self::validateFieldsCLASS($classSpecifier, $fieldValues);
			if ($errors !== true){
				$keys = array_keys($errors);
				throw $errors[$keys[0]];
			}
		}
		
		$db = self::getDBCLASS($classSpecifier);
		
		$keys = "";
		$values = "";
		foreach (DBItemField::parseClass($classSpecifier) as $field){
			/** @var DBItemField $field */
			if (array_key_exists($field->name, $fieldValues)){
				$field->appendDBNameAndValueForCreate($fieldValues[$field->name], $keys, $values);
			}
		}
		$query = "INSERT INTO " . $db->quote($classSpecifier->getTableName(), DB::PARAM_IDENT) . " (" . $keys . ") VALUES (" . $values . ")";
		if ($db->query($query) === false){
			echo $query;
			var_dump($db->errorInfo());
			die();
		}
		$newId = $db->lastInsertId();
		foreach (DBItemField::parseClass($classSpecifier) as $field){
			$field->createDependencies($newId, $fieldValues);
		}
		$item = self::fastGetCLASS($classSpecifier, $newId);
		
		foreach (DBItemField::parseClass($classSpecifier) as $field){
			/** @var DBItemField $field */
			$field->performAssignmentsAfterCreation($item, $fieldValues);
		}
		
		return $item;
	}

	/**
	 * Saves all touched DBItem instances.
	 */
	public static function saveAll(){
		foreach (self::$instances as $classes){
			foreach ($classes as $instance){
				if (!$instance->deleted){
					$instance->save();
				}
			}
		}
	}

	// class methods

	/**
	 * Protected constructor. To get an instance of the desired class call DBItem::getCLASS().
	 * @see DBItem::getCLASS()
	 * @param DBItemClassSpecifier $classSpecifier
	 * @param int $DBid
	 * @param string[]|null $data
	 */
	protected function __construct(DBItemClassSpecifier $classSpecifier, $DBid, $data = null){
		$this->db = self::getDBCLASS($classSpecifier);
		$this->specifier = $classSpecifier;
		$this->fields = DBItemField::parseClass($this->specifier);
		$this->table = $this->db->quote($this->specifier->getTableName(), DB::PARAM_IDENT);
		$this->DBid = $DBid;
		if ($data){
			$this->oldValues = $data;
			foreach ($this->fields as $field){
				/** @var DBItemField $field */
				$field->loadDependencies($this);
			}
		}
		else {
			$this->load();
		}
	}

	/**
	 * Loads the item fresh from the database.
	 */
	public function load(){
		if ($this->DBid === 0){
			$data = array();
			foreach ($this->fields as $field){
				/** @var DBItemField $field */
				$data[$field->name] = $field->default;
			}
			$this->changeable = false;
		}
		else {
			$data = $this->db->query("SELECT * FROM " . $this->table . " WHERE `id` = " . $this->DBid);
			$data = $data->fetch(DB::FETCH_ASSOC);
			if (!$data){
				throw new Exception("Entry " . $this->DBid . " not found in " . $this->table);
			}
			
		}
		$this->oldValues = $data;
		foreach ($this->fields as $field){
			/** @var DBItemField $field */
			$field->loadDependencies($this);
		}
	}
	
	/**
	 * Saves the item to the database
	 */
	public function save(){
		if ($this->changed && !$this->deleted){
			$prop = "";
			foreach ($this->fields as $field){
				/** @var DBItemField $field */
				$field->saveDependencies($this);
			}
			foreach ($this->newValues as $name => $value){
				$field = $this->getField($name);
				if ($field && $field->saveDependencies($this)){
					$field->appendDBNameAndValueForUpdate($value, $prop);
					$this->makeRealNewValueOld($field);
				}
			}
			if (strlen($prop) !== 0){
				$this->db->query("UPDATE " . $this->table . " SET " . $prop . " WHERE `id` = " . $this->DBid);
			}
			$this->changed = false;
		}
	}

	/**
	 * Deletes the item from the database. Also all connections to the item are removed.
	 */
	public function delete(){
		if (!$this->deleted){
			foreach ($this->fields as $item){
				/** @var DBItemField $item */
				$item->deleteDependencies($this);
			}
			
			$this->db->query("DELETE FROM  " . $this->table . " WHERE `id` = " . $this->DBid);
			
			unset(self::$instances[$this->specifier->getSpecifiedName()][$this->DBid]);
			$this->deleted = true;
			$this->changed = false;
		}
	}

	/**
	 * Searches in the field options for a given name. If $searchInAllExtenders is true not only the actual extenders are searched but
	 * also all potentional extenders.
	 *
	 * @param string $name Name of the field to be searched.
	 * @param bool $searchAllCollections If the search should also include potentional extenders not only the actual ones.
	 * @param DBItemFieldCollection $collection DO NOT USE - only for recursive call with the extenders
	 * @return DBItemField The field option to the given $name. If the $name is not found null is returned.
	 */
	public function getField($name, $searchAllCollections = false, $collection = null){
		if ($collection === null){
			$collection = $this->fields;
		}
		$item = $collection->getFieldByName($name);
		if ($item){
			return $item;
		}
		
		foreach ($collection as $item){
			/** @var DBItemField $item */
			if ($item instanceof DBItemFieldHasSearchableSubcollection){
				if ($searchAllCollections){
					foreach ($item->getAllSubcollections() as $subcollection){
						$value = $this->getField($name, $searchAllCollections, $subcollection);
						if ($value !== null){
							return $value;
						}
					}
				}
				else {
					$subcollection = $item->getSubcollection($this);
					if ($subcollection !== null){
						$value = $this->getField($name, $searchAllCollections, $subcollection);
						if ($value !== null){
							return $value;
						}
					}
				}
			}
		}
		return null;
	}
	
	/**
	 * Checks if the item has a field with a specific name.
	 *
	 * @param string $name Name of the field to be searched.
	 * @param boolean $searchAllCollections If the search should also include potentional extenders not only the actual ones.
	 * @return boolean If the item has a field with the specific name.
	 */
	public function hasField($name, $searchAllCollections = false){
		return $this->getField($name, $searchAllCollections) !== null;
	}

	/**
	 * Checks if the real value of the field has changed.
	 *
	 * @param DBItemField $field
	 * @return boolean
	 */
	public function realValueChanged(DBItemField $field){
		return array_key_exists($field->name, $this->newValues) &&
			$this->newValues[$field->name] !== array_read_key($field->name, $this->oldValues, $field->default);
	}

	/**
	 * Moves the new real value to the old value array and deletes entry in new value array.
	 * 
	 * @param DBItemField $field
	 */
	public function makeRealNewValueOld(DBItemField $field){
		if ($this->realValueChanged($field)){
			$this->oldValues[$field->name] = $this->newValues[$field->name];
		}
		unset($this->newValues[$field->name]);
	}

	/**
	 * Returns the real value in $oldValues resp. $newValues. If it is not present in any of this arrays the default value for this field is returned
	 * @param DBItemField $field the name
	 * @return mixed
	 */
	public function getRealValue(DBItemField $field){
		$name = $field->name;
		if (array_key_exists($name, $this->newValues)){
			return $this->newValues[$name];
		}
		elseif (array_key_exists($name, $this->oldValues)){
			return $this->oldValues[$name];
		}
		else {
			return $field->default;
		}
	}

	/**
	 * Magic function __get
	 * @param string $name
	 * @return mixed
	 * @throws Exception
	 * @throws InvalidArgumentException
	 */
	public function __get($name){
		if ($this->deleted){
			throw new Exception("Deleted item can not be accessed.");
		}
		if ($name === "DBid"){
			return $this->DBid;
		}
		if (array_key_exists($name, $this->processedValueCache)){
			return $this->processedValueCache[$name];
		}
		
		$field = $this->getField($name, true);
		if ($field === null){
			$getterName = "get" . ucfirst($name);
			if (method_exists($this, $getterName)){
				return call_user_func(array($this, $getterName));
			}
			throw new InvalidArgumentException("No property " . $name . " found.");
		}
		else {
			$ret = $field->getValue($this);
			$this->processedValueCache[$name] = $ret;
			return $ret;
		}
	}

	/**
	 * Sets the real value in $oldValues resp. $newValues
	 * @param string $name
	 * @param mixed $value
	 */
	public function setRealValue($name, $value){
		if (!array_key_exists($name, $this->oldValues)){
			$this->oldValues[$name] = $value;
		}
		elseif (array_key_exists($name, $this->newValues)){
			$this->newValues[$name] = $value;
		}
		elseif ($value !== $this->oldValues[$name]){
			$this->newValues[$name] = $value;
			$this->changed = true;
		}
	}
	
	/**
	 * Clears the processed value cache.
	 * 
	 * @param string|null $name
	 */
	public function clearProcessedValueCache($name = null){
		if ($name === null){
			$this->processedValueCache = array();
		}
		else {
			unset ($this->processedValueCache[$name]);
		}
	}

	/**
	 * Magic function __set
	 * @param string $name
	 * @param mixed $value
	 * @throws Exception
	 * @throws InvalidArgumentException
	 */
	public function __set($name, $value){
		if ($this->deleted){
			throw new Exception("Deleted item can not be accessed.");
		}
		if (!$this->changeable){
			throw new Exception("This item can not be changed.");
		}
		
		$this->clearProcessedValueCache($name);

		$field = $this->getField($name, false);
		if ($field === null){
			$setterName = "set" . ucfirst($name);
			if (method_exists($this, $setterName)){
				call_user_func(array($this, $setterName), $value);
			}
			else {
				throw new InvalidArgumentException("No property " . $name . " found.");
			}
		}
		else {
			$field->setValue($this, $value);
		}
	}

	/**
	 * Magic function __call
	 * @param type $name
	 * @param type $arguments
	 * @return type
	 * @throws BadMethodCallException
	 */
	public function __call($name, $arguments){
		if (method_exists($this, $name . "CLASS")){
			array_unshift($arguments, $this->specifier);
			return call_user_func_array(array($this, $name . "CLASS"), $arguments);
		}
		throw new BadMethodCallException("No method called " . $name);
	}
	
	/**
	 * only PHP >= 5.3 (__callStatic itself AND late static binding)
	 * @param type $name
	 * @param type $arguments
	 * @return type
	 * @throws BadMethodCallException
	 */
	public static function __callStatic($name, $arguments){
		$class = get_called_class();
		if (method_exists($class, $name . "CLASS")){
			array_unshift($arguments, $class);
			return call_user_func_array(array($class, $name . "CLASS"), $arguments);
		}
		throw new BadMethodCallException("No method called " . $name);
	}
	
	/**
	 * Implementation of the JsonSerializable interface
	 * @return array
	 */
	public function jsonSerialize(){
		$arr = array("id" => $this->DBid);
		foreach ($this->fields as $field){
			if ($field->jsonable){
				$arr[$field->name] = $field->getValue($this);
			}
		}
		return $arr;
	}
}

register_shutdown_function(array("DBItem", "saveAll"));

?>

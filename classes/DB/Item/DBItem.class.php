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
abstract class DBItem extends DBItemFriends{
	/**
	 * Default order for the items if selected by @see DBItem::getByCondition() without third parameter.
	 * @var String
	 */
	public static $defaultOrder = false;
	
	/**
	 * Stores all instances of ANY DBItem.
	 * @var DBItem[]
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
	
	// static class functions
	
	/**
	 * Get the DBItem of type $class with ID $id.
	 * @param string|DBItemClassSpecifier $classSpecifier
	 * @param int $id
	 * @return DBItem of type $class
	 */
	public static function getCLASS($classSpecifier, $id){
		#check for valid ID
		if (!is_int($id) && !ctype_digit($id)) return null;
		$id = (int) $id;
		
		$classSpecifier = DBItemClassSpecifier::make($classSpecifier);
		$className = $classSpecifier->getClassName();
		$specifiedName = $classSpecifier->getSpecifiedName();

		#check if $class is an DBItem
		if (!is_subclass_of($className, "DBItem")) return null;

		if (!array_key_exists($specifiedName, self::$instances)){
			self::$instances[$specifiedName] = array();
		}
		if (!array_key_exists($id, self::$instances[$specifiedName])){
			self::$instances[$specifiedName][$id] = new $className($classSpecifier, $id);
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
		$db = DB::getInstance();

		$sql = "SELECT `id` FROM " . $db->quote($classSpecifier->getTableName(), DB::PARAM_IDENT);
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
				$ret[] = self::getCLASS($classSpecifier, $row["id"]);
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

		$db = DB::getInstance();
		$keys = "";
		$values = "";
		foreach (DBItemField::parseClass($classSpecifier) as $field){
			/* @var $field DBItemField */
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
		$item = self::getCLASS($classSpecifier, $newId);
		
		foreach (DBItemField::parseClass($classSpecifier) as $field){
			/* @var $field DBItemField */
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
	 */
	protected function __construct(DBItemClassSpecifier $classSpecifier, $DBid){
		$this->db = DB::getInstance();
		$this->specifier = $classSpecifier;
		$this->table = $this->db->quote($this->specifier->getTableName(), DB::PARAM_IDENT);
		$this->DBid = $DBid;
		$this->load();
	}

	/**
	 * Loads the item fresh from the database.
	 */
	public function load(){
		if ($this->DBid === 0){
			$data = array();
			foreach (DBItemField::parseClass($this->specifier) as $field){
				/* @var $field DBItemField */
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
		foreach (DBItemField::parseClass($this->specifier) as $field){
			/* @var $field DBItemField */
			$field->loadDependencies($this);
		}
	}
	
	/**
	 * Saves the item to the database
	 */
	public function save(){
		if ($this->changed && !$this->deleted){
			$prop = "";
			foreach ($this->newValues as $name => $value){
				$field = $this->getField($name);
				if ($field && $field->saveDependencies($this)){
					$field->appendDBNameAndValueForUpdate($value, $prop);
					$this->makeRealNewValueOld($field);
				}
			}
//			foreach (DBItemField::parseClass($this->specifier) as $field){
//				/* @var $field DBItemField */
//				if ($field->saveDependencies($this) && array_key_exists($field->name, $this->newValues)){
//					$field->appendDBNameAndValueForUpdate($this->newValues[$field->name], $prop);
//					$this->makeRealNewValueOld($field);
//				}
//			}
			if (count($prop) !== 0){
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
			$this->db->query("DELETE FROM  " . $this->table . " WHERE `id` = " . $this->DBid);
			
			foreach (DBItemField::parseClass($this->specifier) as $item){
				/* @var $item DBItemField */
				$item->deleteDependencies($this);
			}
			
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
			$collection = DBItemField::parseClass($this->specifier);
		}
		foreach ($collection as $item){
			/* @var $item DBItemField */
			if ($item->name === $name){
				return $item;
			}
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
	protected function realValueChanged(DBItemField $field){
		return array_key_exists($field->name, $this->newValues) &&
			$this->newValues[$field->name] !== array_read_key($field->name, $this->oldValues, $field->default);
	}

	/**
	 * Moves the new real value to the old value array and deletes entry in new value array.
	 * 
	 * @param DBItemField $field
	 */
	protected function makeRealNewValueOld(DBItemField $field){
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
	protected function getRealValue(DBItemField $field){
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
		
		$field = $this->getField($name, true);
		if ($field === null){
			$getterName = "get" . ucfirst($name);
			if (method_exists($this, $getterName)){
				return call_user_func(array($this, $getterName));
			}
			throw new InvalidArgumentException("No property " . $name . " found.");
		}
		else {
			return $field->getValue($this);
		}
	}

	/**
	 * Sets the real value in $oldValues resp. $newValues
	 * @param string $name
	 * @param mixed $value
	 */
	protected function setRealValue($name, $value){
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
}

register_shutdown_function(array("DBItem", "saveAll"));

?>

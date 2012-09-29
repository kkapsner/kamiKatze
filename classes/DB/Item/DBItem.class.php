<?php
/**
 * DBItem class definition
 */

/**
 * A DBItem instance represents an entry in a DB. To specifiy the table extend this class in a class that has the name of the table.
 *
 * @author Korbinian Kapsner
 * @todo make extra class for DBItem with extensions
 * @package DB\Item
 */
abstract class DBItem extends ViewableHTML{
	
	/**
	 * Prefix of table name
	 * @var string
	 */
	public static $tablePrefix = "";
	
	/**
	 * Stores all instances of ANY DBItem.
	 * @var array
	 */
	private static $instances = array();

	/**
	 * Stores all extensions for ALL DBItems
	 * @var array
	 */
	protected static $extensions = array();

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
	 * @var array
	 */
	protected $oldValues = array();

	/**
	 * Array of the new values that are set
	 * @var array
	 */
	protected $newValues = array();
	
	// static class functions

	/**
	 * Get the DBItem of type $class with ID $id.
	 * @param string $class
	 * @param int $id
	 * @return DBItem of type $class
	 */
	public static function getCLASS($class, $id){
		#check for valid ID
		if (!is_int($id) && !ctype_digit($id)) return null;
		$id = (int) $id;

		#check if $class is an DBItem
		if (!is_subclass_of($class, "DBItem")) return null;

		if (!array_key_exists($class, self::$instances)){
			self::$instances[$class] = array();
		}
		if (!array_key_exists($id, self::$instances[$class])){
			self::$instances[$class][$id] = new $class($id);
		}

		return self::$instances[$class][$id];
	}

	/**
	 * Returns items of the $class which forfill the $where condition. 
	 * @param string $class
	 * @param string $where
	 * @param string $orderBy
	 * @return DBItemCollection with $class
	 */
	public static function getByConditionCLASS($class, $where = false, $orderBy = false){
		$ret = new DBItemCollection($class);
		$db = DB::getInstance();

		$sql = "SELECT `id` FROM " . $db->quote(self::$tablePrefix . $class, DB::PARAM_IDENT);
		if ($where){
			$sql .= " WHERE " . $where;
		}
		if ($orderBy){
			$sql .= " ORDER BY " . $orderBy;
		}

		foreach ($db->query($sql) as $row){
			$ret[] = self::getCLASS($class, $row["id"]);
		}
		return $ret;
	}
	
	/**
	 *
	 * @param string $name1
	 * @param string $name2
	 * @return string 
	 */
	protected static function getLinkingTableName($name1, $name2){
		$db = DB::getInstance();
		if ($name1 < $name2){
			return $db->quote(self::$tablePrefix . $name1 . "_" . $name2, DB::PARAM_IDENT);
		}
		elseif ($name1 == $name2){
			return $db->quote(self::$tablePrefix . $name1 . "_" . $name2, DB::PARAM_IDENT);
		}
		else {
			return $db->quote(self::$tablePrefix . $name2 . "_" . $name1, DB::PARAM_IDENT);
		}
	}
	
	/**
	 * Gets all connected items by a linking table.
	 * @param string $class Classname of the instances that should be received
	 * @param string $name Name of the field that contains the received instances
	 * @param string $linkedName Name of the field on the other side
	 * @param int $linkedId ID of the instance that links to the instances
	 * @return DBItemCollection with $class
	 */
	protected static function getByLinkingTable($class, $name, $linkedName, $linkedId){
		$ret = new DBItemCollection($class);
		$db = DB::getInstance();
		
		$table = self::getLinkingTableName($name, $linkedName);
		
		$sql = "SELECT " . $db->quote($name . "_id", DB::PARAM_IDENT) .
			" FROM " . $table . 
			" WHERE " . $db->quote($linkedName . "_id", DB::PARAM_IDENT) . " = " . $linkedId;
		$res = $db->query($sql);
		foreach ($res as $row){
			$ret[] = self::getCLASS($class, $row[$name . '_id']);// PHP 5.3: $class::get($row[$class . '_id']);
		}
		return $ret;
	}

	/**
	 * Sets a connection in a linking table.
	 * @param string $name
	 * @param string $linkedName
	 * @param int $linkedId
	 * @param int $id
	 */
	protected static function setInLinkingTable($name, $linkedName, $linkedId, $id){
		$db = DB::getInstance();

		$table = self::getLinkingTableName($name, $linkedName);

		$db->query("INSERT INTO " . $table . " (" . $db->quote($linkedName . "_id", DB::PARAM_IDENT) . ", " . $db->quote($name . "_id", DB::PARAM_IDENT) . ") VALUES (" . $linkedId . ", " . $id . ")");
	}

	/**
	 * Removes a connection in a linking table.
	 * @param string $name
	 * @param string $linkedName
	 * @param int $linkedId
	 * @param int $id
	 */
	protected static function removeInLinkingTable($name, $linkedName, $linkedId, $id){
		$db = DB::getInstance();

		$table = self::getLinkingTableName($name, $linkedName);

		$db->query("DELETE FROM " . $table . " WHERE " . $db->quote($linkedName . "_id", DB::PARAM_IDENT) . " = " . $linkedId . " AND " . $db->quote($name . "_id", DB::PARAM_IDENT) . " = " . $id);
	}
	
	/**
	 * Checks if the $value is valid for a field.
	 * @param DBItemFieldOption $fieldOption Field option for the validated field
	 * @param mixed $value Privided value.
	 * @return DBItemValidationException|null If no error occures null is returned
	 */
	protected static function validateField(DBItemFieldOption $fieldOption, $value){
		if ($value === null && !$fieldOption->null){
			return new DBItemValidationException(
				"Field " . $fieldOption->displayName . " may not be NULL.",
				DBItemValidationException::WRONG_NULL
			);
		}
		// TODO: type checking
		if ($fieldOption->type === "enum" && !in_array($value, $fieldOption->typeExtension, true)){
			return new DBItemValidationException(
				"Field " . $fieldOption->displayName . " must be one of " . implode(", ", $fieldOption->typeExtension) . " " . $fieldOption->regExp . " but '" . $value . "' provided.",
				DBItemValidationException::WRONG_VALUE
			);
		}
		if ($fieldOption->regExp && !preg_match($fieldOption->regExp, $value)){
			return new DBItemValidationException(
				"Field " . $fieldOption->displayName . " must match regular expression " . $fieldOption->regExp . " but '" . $value . "' provided.",
				DBItemValidationException::WRONG_REGEXP
			);
		}
		return null;
	}

	/**
	 * Checks all fields in a array $fieldOptions for validity.
	 * @param array $fieldOptions All fields to be checked.
	 * @param array $values Provided values
	 * @return array Array of all occuring errors.
	 */
	protected static function validateFieldsByFieldOptions($fieldOptions, $values){
		$errors = array();
		foreach ($fieldOptions as $fieldOption){
			/* @var $fieldOption DBItemFieldOption */
			$error = null;
			if (array_key_exists($fieldOption->name, $values)){
				$error = self::validateField($fieldOption, $values[$fieldOption->name]);
			}
			elseif ($fieldOption->default === null && !$fieldOption->null) {
				$error = new DBItemValidationException("Field " . $fieldOption->displayName . " is reqired.", DBItemValidationException::WRONG_MISSING);
			}
			if ($error !== null){
				$errors[$fieldOption->name] = $error;
			}
			elseif ($fieldOption->extender){
				$extenderValue = array_read_key($fieldOption->name, $values, $fieldOption->default);
				if ($extenderValue !== null){
					$errors = array_merge(
						$errors,
						self::validateFieldsByFieldOptions($fieldOption->extensionFieldOptions[$extenderValue], $values)
					);
				}
			}
		}
		return $errors;
	}

	/**
	 * Checks if the provided $values are valid for the specific $class
	 * @param string $class The class to be checked.
	 * @param array $values The provided values.
	 * @return true|array true if everything is fine or an array of all occuring errors.
	 */
	public static function validateFieldsCLASS($class, $values){
		$errors = self::validateFieldsByFieldOptions(DBItemFieldOption::parseClass($class), $values);
		if (count($errors) == 0){
			return true;
		}
		else {
			return $errors;
		}
	}
	
	/**
	 * Creates all the DB entries in the extender tables.
	 * @param DBItemFieldOption $fieldOption Option of the extender field.
	 * @param int $newId ID of the new created item.
	 * @param array $fields Contains the provided values to create the item.
	 * @return array Array containing all field options where the type is DBItemFieldOption::DB_ITEM
	 */
	private static function createExtenderCLASS(DBItemFieldOption $fieldOption, $newId, $fields){
		$db = DB::getInstance();
		$keys = array($db->quote('id', DB::PARAM_IDENT));
		$values = array($newId);
		$dbItemFields = array();
		$extenderValue = array_read_key($fieldOption->name, $fields, $fieldOption->default);
		if ($extenderValue !== null){
			foreach ($fieldOption->extensionFieldOptions[$extenderValue] as $item){
				/* @var $item DBItemFieldOption */
				if (array_key_exists($item->name, $fields)){
					if ($item->type === DBItemFieldOption::DB_ITEM){
						$dbItemFields[] = $item;
					}
					else {
						$keys[] = $db->quote($item->name, DB::PARAM_IDENT);
						if ($item->null && $fields[$item->name] === ""){
							$values[] = "NULL";
						}
						else {
							$values[] = $db->quote($fields[$item->name], DB::PARAM_STR);
						}
					}
				}
				if ($item->extender){
					$dbItemFields = array_merge($dbItemFields, self::createExtenderCLASS($item, $newId, $fields));
				}
			}
			$db->query("INSERT INTO " . $db->quote(self::$tablePrefix . $extenderValue, DB::PARAM_IDENT) . " (" . implode(", ", $keys) . ") VALUES (" . implode(", ", $values) . ")");

		}
		return $dbItemFields;
	}
	/**
	 * Creates a new instance of the specific $class with the values in the $fields
	 * @param string $class Classname of the new instance
	 * @param array $fields field values
	 * @return DBItem of type $class 
	 */
	public static function createCLASS($class, $fields = array()){
		$errors = self::validateFieldsCLASS($class, $fields);
		if ($errors !== true){
			$keys = array_keys($errors);
			throw $errors[$keys[0]];
		}

		$db = DB::getInstance();
		$keys = array();
		$values = array();
		$dbItemFields = array();
		foreach (DBItemFieldOption::parseClass($class) as $item){
			/* @var $item DBItemFieldOption */
			if (array_key_exists($item->name, $fields)){
				if ($item->type === DBItemFieldOption::DB_ITEM){
					$dbItemFields[] = $item;
				}
				else {
					$keys[] = $db->quote($item->name, DB::PARAM_IDENT);
					if ($item->null && $fields[$item->name] === ""){
						$values[] = "NULL";
					}
					else {
						$values[] = $db->quote($fields[$item->name], DB::PARAM_STR);
					}
				}
			}
		}
		$db->query("INSERT INTO " . $db->quote(self::$tablePrefix . $class, DB::PARAM_IDENT) . " (" . implode(", ", $keys) . ") VALUES (" . implode(", ", $values) . ")");
		$newId = $db->lastInsertId();
		foreach (DBItemFieldOption::parseClass($class) as $item){
			if ($item->extender){
				$dbItemFields = array_merge($dbItemFields, self::createExtenderCLASS($item, $newId, $fields));
			}
		}
		$item = self::getCLASS($class, $newId);
		
		foreach ($dbItemFields as $fieldOption){
			/* @var $fieldOption DBItemFieldOption */
			switch ($fieldOption->correlation){
				case DBItemFieldOption::ONE_TO_ONE: case DBItemFieldOption::N_TO_ONE:
					$value = self::getCLASS($fieldOption->class,  $fields[$fieldOption->name]);
					break;
				case DBItemFieldOption::ONE_TO_N: case DBItemFieldOption::N_TO_N:
					$value = new DBItemCollection($fieldOption->class);
					foreach ($fields[$fieldOption->name] as $id){
						$value[] = self::getCLASS($fieldOption->class, $id);
					}
					break;
			}
			$item->{$name} = $value;
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

	/**
	 * Adds an extension to a certain $class.
	 * @param string $class
	 * @param DBItemExtension $extension
	 */
	public static function addExtensionCLASS($class, DBItemExtension $extension){
		if ($extension->isValidClass($class)){
			self::$extensions[] = array("class" => $class, "extension" => $extension);
		}
		else {
			throw new InvalidArgumentException("Extension '" . $extension->getName() . "' is not build for class " . $class . ".");
		}
	}
	
	/**
	 * Returns all extensions of a certain $class and all its parent classes.
	 * @param string $class
	 * @return array of DBItemExtension
	 */
	public static function getExtensionsCLASS($class){
		$ret = array();
		foreach (self::$extensions as $extension){
			if ($extension["class"] === $class || is_subclass_of($class, $extension["class"])){
				$ret[] = $extension["extension"];
			}
		}
		return $ret;
	}


	// class methods

	/**
	 * Protected constructor. To get an instance of the desired class call DBItem::getCLASS().
	 * @see DBItem::getCLASS()
	 * @param int $DBid
	 */
	protected function __construct($DBid){
		$this->db = DB::getInstance();
		$this->table = $this->db->quote(self::$tablePrefix . get_class($this), DB::PARAM_IDENT);
		$this->DBid = $DBid;
		$this->load();
	}

	/**
	 * Loads the data of an extender
	 * @param DBItemFieldOption $extenderOption Options that describe the extender.
	 */
	private function loadExtender(DBItemFieldOption $extenderOption){
		$extenderValue = $this->{$extenderOption->name};
		if ($this->DBid === 0){
			$data = array();
			foreach ($extenderOption->extensionFieldOptions[$extenderValue] as $item){
				/* @var $item DBItemFieldOption */
				$data[$item->name] = $item->default;
			}
			$this->changeable = false;
		}
		else {
			if ($extenderValue !== null){
				$data = $this->db->query(
					"SELECT * FROM " .
					$this->db->quote(self::$tablePrefix . $extenderValue, DB::PARAM_IDENT) .
					"WHERE `id` = " . $this->DBid
				);
				$data = $data->fetch(DB::FETCH_ASSOC);
				if (!$data){
					throw new Exception("Invalid database. Please contact administrator. (ID " . $this->DBid . " not found in extender table " . $extenderValue . ")");
				}
			}
		}

		$this->oldValues = array_merge($this->oldValues, $data);
		foreach ($extenderOption->extensionFieldOptions[$extenderValue] as $item){
			/* @var $item DBItemFieldOption */
			if ($item->extender){
				$this->loadExtender($item);
			}
		}
	}
	/**
	 * Loads the item fresh from the database.
	 */
	public function load(){
		if ($this->DBid === 0){
			$data = array();
			foreach (DBItemFieldOption::parseClass(get_class($this)) as $item){
				/* @var $item DBItemFieldOption */
				$data[$item->name] = $item->default;
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
		foreach (DBItemFieldOption::parseClass(get_class($this)) as $item){
			/* @var $item DBItemFieldOption */
			if ($item->extender){
				$this->loadExtender($item);
			}
		}
	}

	/**
	 * Saves the item to the database
	 * @todo save extenders
	 */
	public function save(){
		if ($this->changed && !$this->deleted){
			$prop = array();
			foreach ($this->newValues as $name => $value){
				$prop[] = $this->db->quote($name, DB::PARAM_IDENT) . " = " . ($value === null? "NULL": $this->db->quote($value));

				$this->oldValues[$name] = $value;
				unset($this->newValues[$name]);
			}
			$this->db->query("UPDATE " . $this->table . " SET " . implode(", ", $prop) . " WHERE `id` = " . $this->DBid);
			$this->changed = false;
		}
	}

	/**
	 * Deletes the item from the database. Also all connections to the item are removed.
	 * @todo delete extenders
	 */
	public function delete(){
		if (!$this->deleted){
			$this->db->query("DELETE FROM  " . $this->table . " WHERE `id` = " . $this->DBid);

			foreach (DBItemFieldOption::parseClass(get_class($this)) as $item){
				/* @var $item DBItemFieldOption */
				switch ($item->type){
					case DBItemFieldOption::DB_ITEM:
						switch ($item->correlation){
							case DBItemFieldOption::ONE_TO_ONE: case DBItemFieldOption::N_TO_ONE:
								$this->{$item->name} = null;
								break;
							case DBItemFieldOption::ONE_TO_N: case DBItemFieldOption::N_TO_N:
								$this->{$item->name} = new DBItemCollection($item->class);
								break;
						}
						break;
				}
			}
			
			unset(self::$instances[$class][$this->DBid]);
			$this->deleted = true;
			$this->changed = false;
		}
	}

	/**
	 * Searches in the field options for a given name. If $searchInAllExtenders is true not only the actual extenders are searched but
	 * also all potentional extenders.
	 * @param string $name Name for the field to be searched.
	 * @param bool $searchInAllExtenders If the search should also include potentional extenders not only the actual ones.
	 * @param array $options DO NOT USE - only for recursive call with the extenders
	 * @return DBItemFieldOption The field option to the given $name. If the $name is not found null is returned.
	 */
	private function getFieldOption($name, $searchInAllExtenders = false, $options = null){
		if ($options === null){
			$options = DBItemFieldOption::parseClass(get_class($this));
		}
		foreach ($options as $item){
			/* @var $item DBItemFieldOption */
			if ($item->name === $name){
				return $item;
			}
			if ($item->extender){
				if ($searchInAllExtenders){
					foreach ($item->typeExtension as $extenderValue){
						$value = $this->getFieldOption(
							$name, $searchInAllExtenders, $item->extensionFieldOptions[$extenderValue]
						);
						if ($value !== null){
							return $value;
						}
					}
				}
				else {
					$extenderValue = $this->getRealValue($item);
					if ($extenderValue !== null){
						$value = $this->getFieldOption(
							$name, $searchInAllExtenders, $item->extensionFieldOptions[$extenderValue]
						);
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
	 * Returns the real value in $oldValues resp. $newValues. If it is not present in any of this arrays the default value for this field is returned
	 * @param DBItemFieldOption $fieldOption the name
	 * @return mixed
	 */
	private function getRealValue(DBItemFieldOption $fieldOption){
		$name = $fieldOption->name;
		if (array_key_exists($name, $this->newValues)){
			return $this->newValues[$name];
		}
		if (array_key_exists($name, $this->oldValues)){
			return $this->oldValues[$name];
		}

		return $fieldOption->default;
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
		
		$fieldOption = $this->getFieldOption($name, true);
		if ($fieldOption === null){
			throw new InvalidArgumentException("No property " . $name . " found.");
		}
		else {
			switch ($fieldOption->type){
				case DBItemFieldOption::DB_ITEM:
					switch ($fieldOption->correlation){
						case DBItemFieldOption::ONE_TO_ONE: case DBItemFieldOption::N_TO_ONE:
							$value = $this->getRealValue($fieldOption);
							if ($value !== null){
								return self::getClass($fieldOption->class, $value);
							}
							else{
								return null;
							}
							break;
						case DBItemFieldOption::ONE_TO_N:
							return self::getByConditionCLASS(
								$fieldOption->class,
								$this->db->quote($fieldOption->correlationName, DB::PARAM_IDENT) . " = " . $this->DBid
							);
							break;
						case DBItemFieldOption::N_TO_N:
							return self::getByLinkingTable(
								$fieldOption->class,
								$fieldOption->name,
								$fieldOption->correlationName,
								$this->DBid
							);
							break;
					}
					break;
				default:
					return $this->getRealValue($fieldOption);
			}
		}
	}

	/**
	 * Sets the real value in $oldValues resp. $newValues
	 * @param string $name
	 * @param mixed $value
	 */
	private function setRealValue($name, $value){
		if (array_key_exists($name, $this->newValues)){
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

		$fieldOption = $this->getFieldOption($name, false);
		if ($fieldOption === null){
			throw new InvalidArgumentException("No property " . $name . " found.");
		}
		else {
			switch ($fieldOption->type){
				case DBItemFieldOption::DB_ITEM:
					switch ($fieldOption->correlation){
						case DBItemFieldOption::ONE_TO_ONE:
							//remove old dependency
							$valueItem = $this->{$name};
							if ($valueItem !== null){
								$valueItem->{$fieldOption->correlationName} = null;
							}
							//desired missing break
						case DBItemFieldOption::N_TO_ONE:
							if ($value === null){
								$this->{$name} = null;
							}
							elseif ($value instanceof $fieldOption->class){
								if ($this->getRealValue($fieldOption) !== $value->DBid){
									$this->setRealValue($name, $value->DBid);
									if ($fieldOption->correlation === DBItemFieldOption::ONE_TO_ONE){
										$value->{$fieldOption->correlationName} = null;
										$value->setRealValue($fieldOption->correlationName, $this->DBid);
									}
								}
							}
							else {
								throw new InvalidArgumentException("Property " . $name . " is no " . $fieldOption->class . ".");
							}
							break;
						case DBItemFieldOption::ONE_TO_N:
							if (is_a($value, "DBItemCollection")){
								if ($value->getClass() !== $fieldOption->class && is_subclass_of($value->getClass(), $fieldOption->class)){
									throw new InvalidArgumentException("Property " . $name . " contains a non " . $fieldOption->class . ".");
								}
								$oldValues = $this->{$name};
								$newValue = array();

								foreach ($value as $valueItem){
									if (($pos = $oldValues->search($valueItem, true)) !== false){
										$oldValues->splice($pos, 1);
									}
									else {
										$newValue[] = $valueItem;
									}
								}
								foreach ($newValue as $valueItem){
									$valueItem->{$fieldOption->correlationName} = $this;
								}

								foreach ($oldValues as $valueItem){
									$valueItem->{$fieldOption->correlationName} = null;
								}
							}
							else {
								throw new InvalidArgumentException("Property " . $name . " is not an DBItemCollection.");
							}
							break;
						case DBItemFieldOption::N_TO_N:
							if (is_a($value, "DBItemCollection")){
								if ($value->getClass() !== $fieldOption->class && is_subclass_of($value->getClass(), $fieldOption->class)){
									throw new InvalidArgumentException("Property " . $name . " contains a non " . $fieldOption->class . ".");
								}
								$oldValues = $this->{$name};
								$newValue = array();

								foreach ($value as $valueItem){
									if (($pos = $oldValues->search($valueItem, true)) !== false){
										$oldValues->splice($pos, 1);
									}
									else {
										$newValue[] = $valueItem;
									}
								}
								foreach ($newValue as $valueItem){
									self::setInLinkingTable($name, $fieldOption->correlationName, $this->DBid, $valueItem->DBid);
								}

								foreach ($oldValues as $valueItem){
									self::removeInLinkingTable($name, $fieldOption->correlationName, $this->DBid, $valueItem->DBid);
								}
							}
							else {
								throw new InvalidArgumentException("Property " . $name . " is not an DBItemCollection.");
							}
							break;
					}
					break;
				default:
					$this->setRealValue($name, $value);
			}
		}
	}

	/**
	 * Amgic function __call
	 * @param type $name
	 * @param type $arguments
	 * @return type
	 * @throws BadMethodCallException
	 */
	public function __call($name, $arguments){
		if (method_exists("DBItem", $name . "CLASS")){
			array_unshift($arguments, get_class($this));
			return call_user_func_array(array("DBItem", $name . "CLASS"), $arguments);
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
		if (method_exists("DBItem", $name . "CLASS")){
			array_unshift($arguments, get_called_class());
			return call_user_func_array(array("DBItem", $name . "CLASS"), $arguments);
		}
		throw new BadMethodCallException("No method called " . $name);
	}
}

register_shutdown_function(array("DBItem", "saveAll"));

/**
 * Exception for a sanitation error.
 * @author Korbibian Kapsner
 * @package DB\Item
 */
class DBItemValidationException extends UnexpectedValueException{
	/**
	 * Error code if the value was null where it was not allowed
	 */
	const WRONG_NULL = 0;
	/**
	 * Error code if the value was the wrong type
	 */
	const WRONG_TYPE = 1;
	/**
	 * Error code if the value was wrong. E.g. if the field is a enum and the value was not allowed.
	 */
	const WRONG_VALUE = 2;
	/**
	 * Error code if the value did not match the provided regular expression.
	 */
	const WRONG_REGEXP = 3;
	/**
	 * Error code if the value is missing but required
	 */
	const WRONG_MISSING = 4;
}
?>

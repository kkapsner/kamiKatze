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
	 * If the item is new
	 * @var bool
	 */
	protected $new = false;

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
	 *
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
	 *
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
	 *
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
	 *
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
	 * Creates a new instance of the specific $class with the values in the $fields
	 * @param string $class Classname of the new instance
	 * @param array $fields field values
	 * @return DBItem of type $class 
	 */
	public static function createCLASS($class, $fields = array()){
		$db = DB::getInstance();
		$keys = array();
		$values = array();
		$dbItemFields = array();
		foreach (DBItemFieldOption::parseClass($class) as $item){
			/* @var $item DBItemFieldOption */
			if (array_key_exists($item->name, $fields)){
				if ($item->type === DBItemFieldOption::DB_ITEM){
					switch ($item->correlation){
						case DBItemFieldOption::ONE_TO_ONE: case DBItemFieldOption::N_TO_ONE:
							$dbItemFields[$item->name] = self::getCLASS($item->class,  $fields[$item->name]);
							break;
						case DBItemFieldOption::ONE_TO_N: case DBItemFieldOption::N_TO_N:
							$dbItemFields[$item->name] = new DBItemCollection($item->class);
							foreach ($fields[$item->name] as $id){
								$dbItemFields[$item->name][] = self::getCLASS($item->class, $id);
							}
							break;
					}
					
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
		#TODO: create extenders
		$item = self::getCLASS($class, $newId);
		
		foreach ($dbItemFields as $name => $value){
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
					throw new Exception("Invalid database contact administrator. (" . $this->DBid . " not found in extender table " . $extenderValue . ")");
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
	 * Returns the real value in $oldValues resp. $newValues
	 * @param string|DBItemFieldOption $name the name
	 * @return mixed
	 */
	private function getRealValue($name){
		if ($name instanceof DBItemFieldOption){
			$defaultReturn = $name->default;
			$name = $name->name;
		}
		else {
			$defaultReturn = null;
		}
		
		if (array_key_exists($name, $this->newValues)){
			return $this->newValues[$name];
		}
		if (array_key_exists($name, $this->oldValues)){
			return $this->oldValues[$name];
		}

		return $defaultReturn;
		#throw new InvalidArgumentException("No property " . $name . " found.");
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
			$options = DBItemFieldOption::parseClass($class);
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
					$extenderValue = $this->getRealValue($item->name);
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
	 *
	 * @param array $options Array of DBItemFieldOptions to be searched in
	 * @param string $name the name to search for
	 * @param bool $success if the search was successful
	 * @return mixed value of the field if found. Null if not found - BUT check $success if the field was found
	 */
	private function getBySearchInFieldOptions($options, $name, &$success = true){
		$success = true;
		foreach ($options as $item){
			/* @var $item DBItemFieldOption */
			if ($item->name === $name){
				switch ($item->type){
					case DBItemFieldOption::DB_ITEM:
						switch ($item->correlation){
							case DBItemFieldOption::ONE_TO_ONE: case DBItemFieldOption::N_TO_ONE:
								$value = $this->getRealValue($name);
								if ($value !== null){
									return self::getClass($item->class, $value);
								}
								else{
									return null;
								}
								break;
							case DBItemFieldOption::ONE_TO_N:
								return self::getByConditionCLASS($item->class, $this->db->quote($item->correlationName, DB::PARAM_IDENT) . " = " . $this->DBid);
								break;
							case DBItemFieldOption::N_TO_N:
								return self::getByLinkingTable($item->class, $item->name, $item->correlationName, $this->DBid);
								break;
						}
						break;
					default:
						return $this->getRealValue($name);
				}
			}
			if ($item->extender){
				$extenderValue = $this->getRealValue($item->name);
				if ($extenderValue !== null){
					$value = $this->getBySearchInFieldOptions(
						$item->extensionFieldOptions[$extenderValue], $name, $success
					);
					if ($success){
						return $value;
					}
				}
				foreach ($item->typeExtension as $possibleExtenderValue){
					if ($possibleExtenderValue !== $extenderValue){
						$value = $this->getBySearchInFieldOptions(
							$item->extensionFieldOptions[$possibleExtenderValue], $name, $success
						);
						if ($success){
							return $value;
						}
					}
				}
			}
		}
		$success = false;
		return null;
	}

	public function __get($name){
		if ($this->deleted){
			throw new Exception("Deleted item can not be accessed.");
		}
		if ($name === "DBid"){
			return $this->DBid;
		}
		/*
		$class = get_class($this);

		$value = $this->getBySearchInFieldOptions(DBItemFieldOption::parseClass($class), $name, $success);
		if ($success){
			return $value;
		}*/
		$fieldOption = $this->getFieldOption($name, true);
		if ($fieldOption === null){
			throw new InvalidArgumentException("No property " . $name . " found.");
		}
		else {
			return $this->getRealValue($fieldOption);
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
	 *
	 * @param array $options Array of DBItemFieldOptions to be searched in
	 * @param type $name the name to search for
	 * @param mixed $value the value to be set
	 * @param type $success if the search was successful
	 */
	private function setBySearchInFieldOptions($options, $name, $value, &$success){
		$success = true;
		foreach ($options as $item){
			/* @var $item DBItemFieldOption */
			if ($item->name === $name){
				switch ($item->type){
					case DBItemFieldOption::DB_ITEM:
						switch ($item->correlation){
							case DBItemFieldOption::ONE_TO_ONE:
								$valueItem = $this->{$name};
								if ($valueItem !== null){
									$valueItem->{$item->correlationName} = null;
								}
							case DBItemFieldOption::N_TO_ONE:
								if ($value === null){
									$this->{$name} = null;
								}
								elseif ($value instanceof $item->class){
									if ($this->getRealValue($name) !== $value->DBid){
										$this->setRealValue($name, $value->DBid);
										if ($item->correlation === DBItemFieldOption::ONE_TO_ONE){
											$value->{$item->correlationName} = null;
											$value->setRealValue($item->correlationName, $this->DBid);
										}
									}
								}
								else {
									throw new InvalidArgumentException("Property " . $name . " is no " . $item->class . ".");
								}
								break;
							case DBItemFieldOption::ONE_TO_N:
								if (is_a($value, "DBItemCollection")){
									if ($value->getClass() !== $item->class && is_subclass_of($value->getClass(), $item->class)){
										throw new InvalidArgumentException("Property " . $name . " contains a non " . $item->class . ".");
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
										$valueItem->{$item->correlationName} = $this;
									}

									foreach ($oldValues as $valueItem){
										$valueItem->{$item->correlationName} = null;
									}
								}
								else {
									throw new InvalidArgumentException("Property " . $name . " is not an DBItemCollection.");
								}
								break;
							case DBItemFieldOption::N_TO_N:
								if (is_a($value, "DBItemCollection")){
									if ($value->getClass() !== $item->class && is_subclass_of($value->getClass(), $item->class)){
										throw new InvalidArgumentException("Property " . $name . " contains a non " . $item->class . ".");
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
										self::setInLinkingTable($name, $item->correlationName, $this->DBid, $valueItem->DBid);
									}

									foreach ($oldValues as $valueItem){
										self::removeInLinkingTable($name, $item->correlationName, $this->DBid, $valueItem->DBid);
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

				#exit foreach-loop AND function
				return;
			}
			if ($item->extender){
				$extenderValue = $this->getRealValue($item->name);
				if ($extenderValue !== null){
					$this->setBySearchInFieldOptions(
						$item->extensionFieldOptions[$extenderValue], $name, $value, $success
					);
					if ($success){
						return;
					}
				}
			}
		}
		$success = false;
	}

	public function __set($name, $value){
		if ($this->deleted){
			throw new Exception("Deleted item can not be accessed.");
		}
		if (!$this->changeable){
			throw new Exception("This item can not be changed.");
		}
		$class = get_class($this);
		$this->setBySearchInFieldOptions(DBItemFieldOption::parseClass($class), $name, $value, $success);
		if (!$success){
			throw new InvalidArgumentException("No property " . $name . " found.");
		}
	}

	public function __call($name, $arguments){
		if (method_exists("DBItem", $name . "CLASS")){
			array_unshift($arguments, get_class($this));
			return call_user_func_array(array("DBItem", $name . "CLASS"), $arguments);
		}
		throw new BadMethodCallException("No method called " . $name);
	}

	# only PHP >= 5.3 (__callStatic itself AND late static binding)
	public static function __callStatic($name, $arguments){
		if (method_exists("DBItem", $name . "CLASS")){
			array_unshift($arguments, get_called_class());
			return call_user_func_array(array("DBItem", $name . "CLASS"), $arguments);
		}
		throw new BadMethodCallException("No method called " . $name);
	}
}

register_shutdown_function(array("DBItem", "saveAll"));

?>

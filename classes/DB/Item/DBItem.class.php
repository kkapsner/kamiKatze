<?php

/*
 *
 *
 */

/**
 * Description of DBItem
 *
 * @author kkapsner
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

	protected
		/**
		 * @var DB
		 */
		$db,
		/**
		 * @var string
		 */
		$table,
		/**
		 * @var int
		 */
		$DBid = null,
		/**
		 * @var bool
		 */
		$new = false,
		/**
		 * @var bool
		 */
		$changed = false,
		/**
		 * @var bool
		 */
		$changeable = true,
		/**
		 * @var bool
		 */
		$deleted = false,
		/**
		 * @var array
		 */
		$oldValues = array(),
		/**
		 * @var array
		 */
		$newValues = array()
	;
	
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
	 * @param string $class
	 * @param string $name
	 * @param string $linkedName
	 * @param int $linkedId
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
		$ret = array();
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
		$ret = array();
		$db = DB::getInstance();

		$table = self::getLinkingTableName($name, $linkedName);

		$db->query("DELETE FROM " . $table . " WHERE " . $db->quote($linkedName . "_id", DB::PARAM_IDENT) . " = " . $linkedId . " AND " . $db->quote($name . "_id", DB::PARAM_IDENT) . " = " . $id);
	}
	
	/**
	 *
	 * @param string $class
	 * @param array $fields
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
					$values[] = $db->quote($fields[$item->name], DB::PARAM_STR);
				}
			}
		}
		$db->query("INSERT INTO " . $db->quote(self::$tablePrefix . $class, DB::PARAM_IDENT) . " (" . implode(", ", $keys) . ") VALUES (" . implode(", ", $values) . ")");
		$item = self::getCLASS($class, $db->lastInsertId());
		
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
			throw new InvalidArgumentException("Extension " . $extension->getName() . " is not build for class " . $class . ".");
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

	protected function __construct($DBid){
		$this->db = DB::getInstance();
		$this->table = $this->db->quote(self::$tablePrefix . get_class($this), DB::PARAM_IDENT);
		$this->DBid = $DBid;
		$this->load();
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
			$this->oldValues = $data;
			$this->changeable = false;
		}
		else {
			$data = $this->db->query("SELECT * FROM " . $this->table . " WHERE `id` = " . $this->DBid);
			$data = $data->fetch(DB::FETCH_ASSOC);
			if (!$data){
				throw new Exception("Entry " . $this->DBid . " not found in " . $this->table);
			}
			$this->oldValues = $data;
		}
	}

	/**
	 * Saves the item to the database
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
	 * @param string $name
	 * @return mixed
	 */
	private function getRealValue($name){
		
		if (array_key_exists($name, $this->newValues)){
			return $this->newValues[$name];
		}
		if (array_key_exists($name, $this->oldValues)){
			return $this->oldValues[$name];
		}
		
		throw new InvalidArgumentException("No property " . $name . " found.");
	}
	
	public function __get($name){
		if ($this->deleted){
			throw new Exception("Deleted item can not be accessed.");
		}
		if ($name === "DBid"){
			return $this->DBid;
		}
		
		$class = get_class($this);
		
		foreach (DBItemFieldOption::parseClass($class) as $item){
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
		}
		
		throw new InvalidArgumentException("No property " . $name . " found.");
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

	public function __set($name, $value){
		if ($this->deleted){
			throw new Exception("Deleted item can not be accessed.");
		}
		if (!$this->changeable){
			throw new Exception("This item can not be changed.");
		}
		$class = get_class($this);

		foreach (DBItemFieldOption::parseClass($class) as $item){
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
		}

		throw new InvalidArgumentException("No property " . $name . " found.");
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

<?php
/**
 * DBItemFieldDBItem definition file
 */

/**
 * Representation of a DBItem field
 *
 * @author Korbinian Kapsner
 * @package DB\Item\Field
 */
class DBItemFieldDBItem extends DBItemField implements DBItemFieldSearchable{
	const ONE_TO_ONE = 0;
	const ONE_TO_N   = 1;
	const N_TO_ONE   = 2;
	const N_TO_N     = 3;

	/**
	 * Returns the table name of a linking table
	 * @param string $name1 Name for one of the linked classes
	 * @param string $name2 Name for the other linked class
	 * @return string the table name
	 */
	protected static function getLinkingTableName($name1, $name2){
		$db = DB::getInstance();
		if ($name1 < $name2){
			return $db->quote(DBItemClassSpecifier::$tablePrefix . $name1 . "_" . $name2, DB::PARAM_IDENT);
		}
		elseif ($name1 == $name2){
			return $db->quote(DBItemClassSpecifier::$tablePrefix . $name1 . "_" . $name2, DB::PARAM_IDENT);
		}
		else {
			return $db->quote(DBItemClassSpecifier::$tablePrefix . $name2 . "_" . $name1, DB::PARAM_IDENT);
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
			$ret[] = DBItem::getCLASS($class, $row[$name . '_id']);// PHP 5.3: $class::get($row[$class . '_id']);
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
	 * If this is not null this field represents another DBItem with this class.
	 * @var string
	 */
	public $class = null;
	/**
	 * The correlation between this DBItem and the other one.
	 * @var int
	 */
	public $correlation = null;
	/**
	 * The field name of this DBItem in the other one.
	 * @var string
	 */
	public $correlationName = null;
	/**
	 * If a change in this field can overwrite this field in an other entry
	 * @var boolean
	 */
	public $canOverwriteOthers = false;
	
	/**
	 * {@inheritdoc}
	 * 
	 * @param mixed $value
	 * @return boolean
	 */
	public function isValidValue($value){
		if (!parent::isValidValue($value)){
			return false;
		}
		switch ($this->correlation){
			case self::ONE_TO_ONE: case self::N_TO_ONE:
				return is_a($value, $this->class);
				break;
			case self::ONE_TO_N: case self::N_TO_N:
				if (is_a($value, "DBItemCollection")){
					$ok = true;
					foreach ($value as $item){
						if (!is_a($item, $this->class)){
							$ok = false;
							break;
						}
					}
					return $ok;
				}
				else {
					return false;
				}
				break;
		}
		return true;
	}

	/**
	 * {@inheritdoc}
	 * 
	 * @param DBItemClassSpecifier $classSpecifier
	 * @param mixed[] $options
	 */
	protected function parseOptions(DBItemClassSpecifier $classSpecifier, $options){
		parent::parseOptions($classSpecifier, $options);

		$this->class = array_read_key("class", $options, null);
		$this->canOverwriteOthers = array_read_key("canOverwriteOthers", $options, $this->canOverwriteOthers);

		// disable default options...
		$this->searchable = false;
		$this->regExp = null;

		# determine correlation
		switch (strtolower(array_read_key("correlation", $options, "1to1"))){
			case "1to1": case "onetoone":
				$this->correlation = self::ONE_TO_ONE;
				break;
			case "1ton": case "oneton":
				$this->correlation = self::ONE_TO_N;
				break;
			case "nto1": case "ntoone":
				$this->correlation = self::N_TO_ONE;
				break;
			case "nton":
				$this->correlation = self::N_TO_N;
				break;
			default:
				$this->correlation = self::ONE_TO_ONE;
		}
		$this->correlationName = array_read_key("correlationName", $options, $classSpecifier->getClassName());
	}

	/**
	 * {@inheritdoc}
	 *
	 * @param array $data
	 * @param array $translatedData
	 */
	public function translateRequestData($data, &$translatedData){
		if (array_key_exists($this->name, $data)){
			switch ($this->correlation){
				case self::ONE_TO_ONE: case self::N_TO_ONE:
					if ($data[$this->name]){
						$translatedData[$this->name] = DBItem::getCLASS($this->class, $data[$this->name]);
					}
					else {
						$translatedData[$this->name] = null;
					}
					break;
				case self::ONE_TO_N: case self::N_TO_N:
					$data = $data[$this->name];
					if (is_array($data) && array_key_exists("present", $data)){
						$value = new DBItemCollection($this->class);
						if (array_key_exists("values", $data)){
							foreach ($data["values"] as $id){
								$value[] = DBItem::getCLASS($this->class, $id);
							}
						}
						$translatedData[$this->name] = $value;
					}
					break;
			}
		}
	}

	/**
	 * {@inheritdoc}
	 *
	 * DBItem field should only be set by member assignments.
	 * @param mixed $value
	 * @return null
	 */
	public function translateToDB($value){
		return null;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @param DBItem $item
	 * @param array $values
	 */
	protected function performAssignmentsAfterCreation(DBItem $item, $values){
		if (array_key_exists($this->name, $values)){
			$item->{$this->name} = $values[$this->name];
		}
	}

	/**
	 * {@inheritdoc}
	 * 
	 * @param DBItem $item
	 */
	protected function deleteDependencies(DBItem $item){
		switch ($this->correlation){
			case self::ONE_TO_ONE: case self::N_TO_ONE:
				$item->{$this->name} = null;
				break;
			case self::ONE_TO_N: case self::N_TO_N:
				$item->{$this->name} = new DBItemCollection($this->class);
				break;
		}
	}

	/**
	 * {@inheritdoc}
	 *
	 * @param DBItem $item
	 * @return null
	 */
	public function getValue(DBItem $item){
		switch ($this->correlation){
			case self::ONE_TO_ONE: case self::N_TO_ONE:
				$value = $item->getRealValue($this);
				if ($value !== null){
					return DBItem::getClass($this->class, $value);
				}
				else{
					return null;
				}
				break;
			case self::ONE_TO_N:
				return DBItem::getByConditionCLASS(
					$this->class,
					DB::getInstance()->quote($this->correlationName, DB::PARAM_IDENT) . " = " . $item->DBid
				);
				break;
			case self::N_TO_N:
				return self::getByLinkingTable(
					$this->class,
					$this->name,
					$this->correlationName,
					$item->DBid
				);
				break;
		}
	}

	/**
	 * {@inheritdoc}
	 *
	 * @param DBItem $item
	 * @param class $value
	 * @throws InvalidArgumentException
	 */
	public function setValue(DBItem $item, $value){

		$oldValue = $this->getValue($item);

		switch ($this->correlation){
			case self::ONE_TO_ONE:
				//remove old dependency
				if ($oldValue !== $value){
					if (!$this->canOverwriteOthers && $value !== null && $value->{$this->correlationName} !== null){
						throw new InvalidArgumentException("Property " . $this->name . " is overwrite protected.");
					}
					if ($oldValue !== null){
						if ($oldValue->{$this->correlationName} === $item){
							$oldValue->setRealValue($this->correlationName, null);
						}
						else {
							$oldValue->__set($this->correlationName, null);
						}
					}
				}
				//desired missing break
			case self::N_TO_ONE:
				if ($value === null){
					$item->setRealValue($this->name, null);
				}
				elseif ($value instanceof $this->class){
					if ($oldValue !== $value){
						$item->setRealValue($this->name, $value->DBid);
						if ($this->correlation === self::ONE_TO_ONE){
							$value->__set($this->correlationName, null);
							$value->setRealValue($this->correlationName, $item->DBid);
						}
					}
				}
				else {
					throw new InvalidArgumentException("Property " . $this->name . " is no " . $this->class . ".");
				}

				/** @todo better saving possible?*/
				if ($value !== null) $value->save();
				if ($oldValue !== null) $oldValue->save();
				$item->save();
				break;
			case self::ONE_TO_N:
				if (is_a($value, "DBItemCollection")){
					if ($value->getClass() !== $this->class && !is_subclass_of($value->getClass(), $this->class)){
						throw new InvalidArgumentException("Property " . $this->name . " contains a non " . $this->class . ".");
					}
					$newValue = array();

					foreach ($value as $valueItem){
						if (($pos = $oldValue->search($valueItem, true)) !== false){
							$oldValue->splice($pos, 1);
						}
						else {
							$newValue[] = $valueItem;
						}
					}
					foreach ($newValue as $valueItem){
						if ($valueItem->{$this->correlationName} !== null && !$this->canOverwriteOthers){
							throw new InvalidArgumentException("Property " . $this->name . " is overwrite protected.");
						}
						$valueItem->{$this->correlationName} = $item;
					}

					foreach ($oldValue as $valueItem){
						$valueItem->{$this->correlationName} = null;
					}
				}
				else {
					throw new InvalidArgumentException("Property " . $this->name . " is not a DBItemCollection.");
				}
				break;
			case self::N_TO_N:
				if (is_a($value, "DBItemCollection")){
					if ($value->getClass() !== $this->class && is_subclass_of($value->getClass(), $this->class)){
						throw new InvalidArgumentException("Property " . $this->name . " contains a non " . $this->class . ".");
					}
					$newValue = array();

					foreach ($value as $valueItem){
						if (($pos = $oldValue->search($valueItem, true)) !== false){
							$oldValue->splice($pos, 1);
						}
						else {
							$newValue[] = $valueItem;
						}
					}
					foreach ($newValue as $valueItem){
						self::setInLinkingTable($this->name, $this->correlationName, $item->DBid, $valueItem->DBid);
					}

					foreach ($oldValue as $valueItem){
						self::removeInLinkingTable($this->name, $this->correlationName, $item->DBid, $valueItem->DBid);
					}
				}
				else {
					throw new InvalidArgumentException("Property " . $this->name . " is not a DBItemCollection.");
				}
				break;
		}
	}

	/**
	 * {@inheritdoc}
	 * 
	 * @param DBItem $value
	 */
	public function getWhere($value){
		$db = DB::getInstance();
		$name = $db->quote($this->name, DB::PARAM_IDENT);
		switch ($this->correlation){
			case self::ONE_TO_ONE: case self::N_TO_ONE:
				return $name . " = " . $db->quote($value->DBid);
			case self::ONE_TO_N:
			case self::N_TO_N:
				throw new Exception("Not implemented");
				return DBItem::getByConditionCLASS(
					$this->class,
					DB::getInstance()->quote($this->correlationName, DB::PARAM_IDENT) . " = " . $item->DBid
				);
				break;
				return self::getByLinkingTable(
					$this->class,
					$this->name,
					$this->correlationName,
					$item->DBid
				);
				break;
		}
	}

}

?>

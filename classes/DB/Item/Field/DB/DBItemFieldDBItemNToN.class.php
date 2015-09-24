<?php
/**
 * DBItemFieldDBItemNToN definition file
 */

/**
 * Representation of a DBItem field with a NToN correlation
 *
 * @author Korbinian Kapsner
 * @package DB\Item\Field
 */
class DBItemFieldDBItemNToN extends DBItemFieldDBItemXToN{
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
	 * If the NtoN correlation can have multiple links between two items.
	 * @var boolean
	 */
	public $canHaveMultipleLinks = false;

	/**
	 * {@inheritdoc}
	 * 
	 * @param DBItemClassSpecifier $classSpecifier
	 * @param mixed[] $properties
	 */
	protected function adoptProperties(DBItemClassSpecifier $classSpecifier, $properties){
		parent::adoptProperties($classSpecifier, $properties);
		
		$this->canHaveMultipleLinks = array_read_key("canHaveMultipleLinks", $properties, $this->canHaveMultipleLinks);
	}

	/**
	 * {@inheritdoc}
	 *
	 * @param DBItem $item
	 * @return null
	 */
	public function getValue(DBItem $item){
		return self::getByLinkingTable(
			$this->class,
			$this->name,
			$this->correlationName,
			$item->DBid
		);
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
	}
}
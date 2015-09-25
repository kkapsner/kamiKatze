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
		if ($name1 <= $name2){
			$tableName = DBItemClassSpecifier::$tablePrefix . $name1 . "_" . $name2;
		}
		else {
			$tableName = DBItemClassSpecifier::$tablePrefix . $name2 . "_" . $name1;
		}
		
		return $db->quote($tableName, DB::PARAM_IDENT);
	}

	
	/**
	 * Name of the linking table.
	 * @var string
	 */
	public $linkingTableName = null;
	
	/**
	 * Name of the field in the linking table that stores the id of the linked
	 * other item.
	 * @var string
	 */
	public $fromFieldInLinkingTable = null;
	
	/**
	 * Name of the field in the linking table that stores the id of this item.
	 * @var string
	 */
	public $toFieldInLinkingTable = null;
	
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
		$this->toFieldInLinkingTable = array_read_key("selfLinkedField", $properties, $this->name . "_id");
		$this->fromFieldInLinkingTable = array_read_key("otherLinkedField", $properties, $this->correlationName . "_id");
		$this->linkingTableName = array_read_key(
			"linkingTableName",
			$properties,
			self::getLinkingTableName($this->name, $this->correlationName)
		);
	}

	/**
	 * {@inheritdoc}
	 *
	 * @param DBItem $item
	 * @return null
	 */
	public function getValue(DBItem $item){
		$ret = new DBItemCollection($this->class);
		$db = DB::getInstance();

		$sql = "SELECT " . $db->quote($this->toFieldInLinkingTable, DB::PARAM_IDENT) .
			" FROM " . $this->linkingTableName .
			" WHERE " . $db->quote($this->fromFieldInLinkingTable, DB::PARAM_IDENT) . " = " . $item->DBid;
		$res = $db->query($sql);
		foreach ($res as $row){
			$ret[] = DBItem::fastGetCLASS($this->classSpecifier, $row[$this->toFieldInLinkingTable]);// PHP 5.3: $class::get($row[$class . '_id']);
		}
		return $ret;
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
			
			$db = DB::getInstance();
			$insertStatement = $db->prepare("INSERT INTO" . $this->linkingTableName . "
				(
					" . $db->quote($this->fromFieldInLinkingTable, DB::PARAM_IDENT) . ",
					" . $db->quote($this->toFieldInLinkingTable, DB::PARAM_IDENT) . "
				) VALUES (
					:from,
					:to
				)");
			$insertStatement->bindValue(":from", $item->DBid, DB::PARAM_INT);

			foreach ($value as $valueItem){
				if (($pos = $oldValue->search($valueItem, true)) !== false){
					$oldValue->splice($pos, 1);
				}
				else {
					$insertStatement->bindValue(":to", $valueItem->DBid, DB::PARAM_INT);
					$insertStatement->execute();
				}
			}
			
			$removeStatement = $db->prepare("DELETE FROM " . $this->linkingTableName . "
				WHERE 
					" . $db->quote($this->fromFieldInLinkingTable, DB::PARAM_IDENT) . " = :from AND
					" . $db->quote($this->toFieldInLinkingTable, DB::PARAM_IDENT) . " = :to");
			$removeStatement->bindValue(":from", $item->DBid, DB::PARAM_INT);
			foreach ($oldValue as $valueItem){
				$removeStatement->bindValue(":to", $valueItem->DBid, DB::PARAM_INT);
				$removeStatement->execute();
			}
		}
		else {
			throw new InvalidArgumentException("Property " . $this->name . " is not a DBItemCollection.");
		}
	}
}